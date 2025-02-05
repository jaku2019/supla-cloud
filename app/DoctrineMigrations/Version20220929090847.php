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

namespace Supla\Migrations;

/**
 * Add supla_scene_operation.user_delay_ms.
 * Add supla_scene_operation.wait_for_completion.
 * Add supla_scene.estimated_execution_time.
 */
class Version20220929090847 extends NoWayBackMigration {
    public function migrate() {
        $this->addSql('ALTER TABLE supla_scene_operation ADD user_delay_ms INT DEFAULT 0 NOT NULL, ADD wait_for_completion TINYINT(1) DEFAULT \'0\' NOT NULL');
        $this->addSql('ALTER TABLE supla_scene ADD estimated_execution_time INT DEFAULT 0 NOT NULL');
        $this->addSql('UPDATE supla_scene_operation SET user_delay_ms = delay_ms');
        $this->addSql('UPDATE supla_scene SET estimated_execution_time = (SELECT COALESCE(SUM(delay_ms), 0) FROM supla_scene_operation WHERE owning_scene_id = supla_scene.id);');
    }
}
