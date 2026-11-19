<?php

declare(strict_types=1);

namespace App\Domain\Household\Exceptions;

use App\Domain\Shared\Exceptions\DomainException;

class DuplicateHouseholdMember extends DomainException {}
