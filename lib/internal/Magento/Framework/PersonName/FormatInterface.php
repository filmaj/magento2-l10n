<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\PersonName;

/**
 * Interface for person name format definition.
 * Any implementation must support declared list of name parts and may provide own.
 *
 * @api
 */
interface FormatInterface
{
    const PART_GIVEN_NAME = 'firstname';

    const PART_FAMILY_NAME = 'lastname';

    const PART_MIDDLE_NAME = 'middlename';

    const PART_NAME_PREFIX = 'prefix';

    const PART_NAME_SUFFIX = 'suffix';

    public function getTemplate(): string;
}