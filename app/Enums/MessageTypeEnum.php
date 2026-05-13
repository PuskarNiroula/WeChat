<?php

namespace App\Enums;

enum MessageTypeEnum:string
{
    case TEXT = 'text';
    case IMAGE = 'image';
    case FILE = 'file';

}
