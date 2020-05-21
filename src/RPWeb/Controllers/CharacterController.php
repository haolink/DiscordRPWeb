<?php

namespace RPWeb\Controllers;

use Pecee\Http\Input\InputFile;
use RPWeb\Common\Kernel;
use RPWeb\Library\Controller;
use RPWeb\Model\Character;
use WebSocket\Client;

class CharacterController extends Controller
{
    const AVATAR_SIZE = 128;

    protected static $MENU = array(
        'active' => 'characters'
    );

    /**
     * Creates a new character.
     *
     * @return 
     */
    public function new()
    {
        return $this->renderForm(-1);
    }

    /**
     * Deletes an existing character.
     *
     * @return 
     */
    public function delete($id)
    {
        if (is_null($id)) {
            redirect(url('dash.index'));
            return;
        }

        $token = input('token');
        $compare = csrf_token('del_char' . $id);

        if ($token != $compare) {
            redirect(url('dash.index'));
            return;
        }

        $char = Character::find($id);

        if (is_null($char) || $char->user_id != $this->getUser()->getId()) {
            redirect(url('dash.index'));
            return;
        }

        $char->delete();
        $this->signalWebsocketChange($this->getUser()->getId());
        
        redirect(url('dash.index'));
    }

    /**
     * Edits a character.
     *
     * @param integer $id
     * @return void
     */
    public function edit($id)
    {
        if (is_null($id)) {
            redirect(url('dash.index'));
            return;
        }

        return $this->renderForm($id);
    }

    /**
     * Renders a character editor form.
     */
    public function renderForm($charId, ?Character $char = null, array $validationErrors = array())
    {
        /** @var Character $char */
        if (is_null($char)) {
            if ($charId < 0) {
                $char = new Character();
                $char->id = -1;
            } else {
                $char = Character::find($charId);
                if (is_null($char)) {
                    redirect(url('dash.index'));
                    return;
                }
                if ($char->user_id != $this->getUser()->getId()) {
                    redirect(url('dash.index'));
                    return;
                }
            }
        } elseif ($charId <= 0) {
            $char->id = -1;
        }

        $this->addTemplateVariable('character', $char);
        $this->addTemplateVariable('errors', $validationErrors);
        return $this->render('Character/editform.html.twig');
    }

    /**
     * Avatar storage path.
     *
     * @var string
     */
    private $avatarOutputPath;

    /**
     * Submits a character form.
     *
     * @return void
     */
    public function submit()
    {
        $config = Kernel::getInstance()->getConfig();
        $this->avatarOutputPath = $config['avatar']['storage_path'];

        $id = input('id');
        $csrf = input('csrf');
        $name = input('name');
        $shortkey = input('shortkey');

        $avatar = request()->getInputHandler()->file('avatar');
        if (!is_null($avatar) && $avatar->getSize() <= 0) {
            $avatar = null;
        }

        if (is_null($id) || is_null($csrf)) {
            redirect(url('dash.index'));
            return;
        }

        $token = csrf_token('edit-char' . $id);
        if ($csrf != $token) {
            redirect(url('dash.index'));
            return;
        }

        if (is_null($name)) {
            $name = '';
        }
        if (is_null($shortkey)) {
            $shortkey = '';
        }

        $char = new Character();
        $newCharacter = true;
        if ($id > 0) 
        {
            $char = Character::find($id);

            if($char->user_id != $this->getUser()->getId()) {
                redirect(url('dash.index'));
                return;
            }

            $newCharacter = false;
        }

        $fullName = trim(preg_replace('/[^\S ]/u', '', $name));

        $char->character_shortname = strtolower(trim($shortkey));
        $char->character_name = $fullName;

        $validationErrors = array();
        if(!preg_match('/^[a-z0-9\_\-]{1,16}$/i', $char->character_shortname)) 
        {
            $validationErrors[] = 'The character short cut may only contain latin default letters and numbers and ' . 
                'mustn\'t be longer than 16 characters.';
        }

        if (mb_strlen($fullName) > 32 || mb_strlen($fullName) < 2 || 
            preg_match('/^(clyde|everyone|discordtag|everyone|here|' .
                '((.*)[\@\#\:](.*))|((.*)\`\`\`(.*))|((.*)[ ]{3,}(.*)))$/ui', $fullName)) 
        {
            $validationErrors[] = 
                'The character name violates the Discord naming guidelines. Character names may be:<br />' . PHP_EOL .
                '-Names must be at least two characters long, they mustn\'t be longer than 32.<br />' . PHP_EOL .                 
                '-Names cannot contain @, #, : or \\`\\`\\`<br />' . PHP_EOL . 
                '-The following nasmes are not allowed: discordtag, everyone, here, clyde.'
            ;
        }

        if ($newCharacter && is_null($avatar)) {
            $validationErrors[] = 
                'You must submit an avatar for a new character!';
        }

        $existing = Character::where([
            ['user_id','=',$this->getUser()->getId()],
            ['character_shortname','=',$char->character_shortname]
        ])->get()->first();

        if (!is_null($existing) && $id != $existing->id) {
            $validationErrors[] = 'You already have a character with this shortcut!';
        }

        $oldAvatar = null;

        if (!is_null($avatar) && count($validationErrors) == 0) {
            $errorCount = count($validationErrors);
            $filename = $this->parseUpload($avatar, $validationErrors);

            if (!is_null($filename)) {                
                $oldAvatar = $char->character_avatar;

                $char->character_avatar = $filename;
            } elseif (count($validationErrors) <= $errorCount) {
                $validationErrors[] = 'Failed saving the avatar!';
            }
        }

        if (count($validationErrors) == 0) {
            if ($newCharacter) {
                $char->user_id = $this->getUser()->getId();
            }
            
            if (!is_null($oldAvatar)) {
                if (file_exists($this->avatarOutputPath . '/' . $oldAvatar)) {
                    unlink($this->avatarOutputPath . '/' . $oldAvatar);
                }
            }

            $char->save();

            $this->signalWebsocketChange($this->getUser()->getId());

            redirect(url('dash.index'));
            return;
        }
        return $this->renderForm($id, $char, $validationErrors);
    }

    /**
     * Signals a change in data to the websocket.
     *
     * @param string $userId
     * @return void
     */
    private function signalWebsocketChange($userId)
    {
        $config = Kernel::getInstance()->getConfig();
        if (!array_key_exists('websocket', $config)) {
            return;
        }
        if (!$config['websocket']['enabled']) {
            return;
        }

        $client = new Client($config['websocket']['url']);
        $client->send(json_encode(array(
            'command' => 'userclear',
            'payload' => $userId
        )));       
        $client->close();
    }

    /**
     * Attempts to parse an uploaded image.
     *
     * @param InputFile $avatar
     * @param array $validationErrors
     * @return string|null
     */
    private function parseUpload(InputFile $avatar, array &$validationErrors) : ?string
    {
        $imageInfo = null;
        $imageSize = null;
        try {
            $imageSize = @getimagesize($avatar->getTmpName(), $imageInfo);
        } catch(\Exception $ex) {
            $imageInfo = null;
            $imageSize = null;
        }

        if (empty($imageSize)) {
            $validationErrors[] = 'Cannot parse attached avatar file.';
            return null;
        }

        $width = $imageSize[0];
        $height = $imageSize[1];

        $mime = $imageSize['mime'];

        if ($width < self::AVATAR_SIZE || $height < self::AVATAR_SIZE) {
            $validationErrors[] = 'The avatar file is too small. It must be at least 128 pixels wide and 128 pixels high.';
            return null;
        }

        $image = imagecreatefromstring(file_get_contents($avatar->getTmpName()));

        $ext = 'jpg';
        if ($mime != 'image/jpeg') {
            imagealphablending($image, false);
            imagesavealpha($image, true);            
            $ext = 'png';
        }

        $cropX = 0;
        $cropEnabled = false;

        $outputWidth = $width;
        $outputHeight = $height;
        if ($width > $height) {
            $cropX = (int)(floor(($width - $height) / 2));
            $outputWidth = $height;
            $cropEnabled = true;
        } else if ($width != $height) {
            $cropX = 0;
            $cropEnabled = true;
            $outputHeight = $width;
        }        

        if ($image === false) {
            $validationErrors[] = 'Failed to crop the avatar image.';
            return null;
        }

        if ($outputHeight != self::AVATAR_SIZE || $outputWidth != self::AVATAR_SIZE || $cropEnabled) {
            $target = imagecreatetruecolor(self::AVATAR_SIZE, self::AVATAR_SIZE);
            
            if ($ext == 'png') {                
                imagealphablending($target, false);
                imagesavealpha($target, true);

                $transparent = imagecolorallocatealpha($target, 255, 255, 255, 127);
                imagefilledrectangle($target, 0, 0, self::AVATAR_SIZE, self::AVATAR_SIZE, $transparent);
            }
            $rs = imagecopyresampled($target, $image, 0, 0, $cropX, 0, self::AVATAR_SIZE, self::AVATAR_SIZE, $outputWidth, $outputHeight);
            $image = null;
            unset ($image);
            $image = $target;

            if (!$rs) {
                $validationErrors[] = 'Failed to resize the avatar image.';
                return null;
            }

            if ($ext == 'png') {
                imagealphablending($target, false);
            }
        }

        $outputFileName = null;
        do {
            $outputFileName = bin2hex(random_bytes(16)) . '.' . $ext;
        } while (file_exists($this->avatarOutputPath . '/' . $outputFileName));

        $saved = false;
        if ($ext == 'png') {
            $saved = imagepng($image, $this->avatarOutputPath . '/' . $outputFileName);
        } else {
            $saved = imagejpeg($image, $this->avatarOutputPath . '/' . $outputFileName, 90);
        }

        if (!$saved) {
            $validationErrors[] = 'Failed to save the avatar image.';
            return null;
        }
        
        return $outputFileName;
    }
}