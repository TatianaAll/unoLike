<?php

namespace App\Enum;

enum Status: string
{
  case IN_PROGRESS = 'in_progress';
  case FINISHED = 'finished';
}