<?php

namespace App\Enums;

enum RoomStatus: string
{
    case Available = 'available';
    case Reserved = 'reserved';
    case Occupied = 'occupied';
}
