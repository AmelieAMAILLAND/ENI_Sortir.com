<?php

namespace App\DTO;

use Symfony\Component\Validator\Constraints as Assert;

class filtersDTO
{


    public function __construct(

        public ?string $nameInput,
        public ?string $siteName,
        public ?string $status,
        public ?string $beginDate,
        public ?string $endDate,
        public ?string $registered,
        public ?bool $isPlanner,
        public ?string $userPseudo,
    )
    {


    }
}