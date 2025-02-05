<?php
namespace SuplaBundle\Model\ChannelActionExecutor;

use Assert\Assertion;
use SuplaBundle\Entity\ActionableSubject;
use SuplaBundle\Enums\ChannelFunctionAction;

class ShutActionExecutor extends StopActionExecutor {
    public function getSupportedAction(): ChannelFunctionAction {
        return ChannelFunctionAction::SHUT();
    }

    public function validateActionParams(ActionableSubject $subject, array $actionParams): array {
        if ($actionParams) {
            Assertion::count($actionParams, 1, 'Invalid number of action parameters');
            Assertion::count(
                array_intersect_key($actionParams, array_flip(['percent', 'percentage'])),
                count($actionParams),
                'Invalid action parameters'
            );

            if (isset($actionParams['percent'])) {
                $actionParams['percentage'] = $actionParams['percent'];
                unset($actionParams['percent']);
            }
            Assertion::keyIsset($actionParams, 'percentage', 'Missing required action param: percent.');
            Assertion::numeric($actionParams['percentage'], 'Invalid percent action param.');
            $actionParams['percentage'] = intval($actionParams['percentage']);
            Assertion::between($actionParams['percentage'], 0, 100, 'Percent should be between 0 and 100.');
        }
        return $actionParams;
    }

    protected function getCharValue(ActionableSubject $subject, array $actionParams = []): int {
        $percent = $actionParams['percentage'] ?? 100;
        return $percent + 10;
    }
}
