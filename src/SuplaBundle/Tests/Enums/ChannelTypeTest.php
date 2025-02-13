<?php
/*
 Copyright (C) AC SOFTWARE SP. Z O.O.

 This program is free software; you can redistribute it and/or
 modify it under the terms of the GNU General Public License
 as published by the Free Software Foundation; either version 2
 of the License, or (at your option) any later version.
 This program is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.
 You should have received a copy of the GNU General Public License
 along with this program; if not, write to the Free Software
 Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
 */

namespace SuplaBundle\Tests\Enums;

use PHPUnit\Framework\TestCase;
use SuplaBundle\Enums\ChannelType;

class ChannelTypeTest extends TestCase {
    public function testEveryTypeHasFunctions() {
        $diff = array_map(function (ChannelType $type) {
            return $type->getKey();
        }, array_diff(ChannelType::values(), array_keys(ChannelType::functions())));
        // relays and bridges have functions calculated from ChannelFunctionBitsFlist enum
        $diff = array_diff($diff, [ChannelType::RELAY()->getKey(), ChannelType::BRIDGE()->getKey()]);
        $this->assertEmpty($diff, 'Have you forgotten to add a functions definition for the new ChannelType value? Missing: '
            . implode(', ', $diff));
    }

    public function testEveryTypeHasCaption() {
        $diff = array_map(function (ChannelType $type) {
            return $type->getKey();
        }, array_diff(ChannelType::values(), array_keys(ChannelType::captions())));
        $this->assertEmpty($diff, 'Have you forgotten to add a caption for the new ChannelType value? Missing: '
            . implode(', ', $diff));
    }

    public function testEveryChannelTypeIdIsDocumented() {
        $ids = implode(',', ChannelType::toArray());
        $source = file_get_contents(\AppKernel::ROOT_PATH . '/../src/SuplaBundle/Enums/ChannelType.php');
        $message = 'Invalid documentation for channel type ids. Enum should be: ' . PHP_EOL . PHP_EOL . $ids . PHP_EOL;
        $this->assertStringContainsString($ids, $source, $message);
    }

    public function testEveryChannelTypeNameIsDocumented() {
        $names = '"' . implode('","', ChannelType::keys()) . '"';
        $source = file_get_contents(\AppKernel::ROOT_PATH . '/../src/SuplaBundle/Enums/ChannelType.php');
        $message = 'Invalid documentation for channel type names. Enum should be: ' . PHP_EOL . PHP_EOL . $names . PHP_EOL;
        $this->assertStringContainsString($names, $source, $message);
    }
}
