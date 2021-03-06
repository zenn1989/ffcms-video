<?php

use engine\permission;
use engine\system;

class api_videoposterdelete_back extends \engine\singleton {

    public function make() {
        if(!permission::getInstance()->have('admin/components/video/add') && !permission::getInstance()->have('admin/components/video/edit'))
            return;
        $id = (int)system::getInstance()->get('id');
        $type = (int)system::getInstance()->get('type');
        if($type == 1) {
            $fpath = root . '/upload/video/catposter/poster_' . $id . '.jpg';
            if(file_exists($fpath))
                @unlink($fpath);
        }
        if($type == 2) {
            $fpath = root . '/upload/video/poster_' . $id . '.jpg';
            if(file_exists($fpath))
                @unlink($fpath);
        }
    }
}