<?php

namespace Predis\Command\Redis;

use Predis\Command\Command as RedisCommand;
use Predis\Command\Traits\Count;
use Predis\Command\Traits\MinMaxModifier;
use Predis\Command\Traits\Numkeys;
use Predis\Command\Traits\Keys;

/**
 * @link https://redis.io/commands/zmpop/
 *
 * Pops one or more elements, that are member-score pairs,
 * from the first non-empty sorted set in the provided list of key names.
 */
class ZMPOP extends RedisCommand
{
    use Numkeys {
        Numkeys::setArguments as setNumkeys;
    }
    use Count {
        Count::setArguments as setCount;
    }
    use MinMaxModifier;
    use Keys;

    protected static $keysArgumentPositionOffset = 0;
    protected static $countArgumentPositionOffset = 2;
    protected static $modifierArgumentPositionOffset = 1;

    public function getId()
    {
        return 'ZMPOP';
    }

    public function setArguments(array $arguments)
    {
        $this->setCount($arguments);
        $arguments = $this->getArguments();

        $this->setNumkeys($arguments);
        $arguments = $this->getArguments();

        $this->resolveModifier(static::$modifierArgumentPositionOffset + 1, $arguments);
        $this->unpackKeysArray(static::$keysArgumentPositionOffset + 1, $arguments);

        parent::setArguments($arguments);
    }

    public function parseResponse($data)
    {
        $key = array_shift($data);

        if (null === $key) {
            return [$key];
        }

        $data = $data[0];
        $parsedData = [];

        for ($i = 0, $iMax = count($data); $i < $iMax; $i++) {
            for ($j = 0, $jMax = count($data[$i]); $j < $jMax; ++$j) {
                if ($data[$i][$j + 1] ?? false) {
                    $parsedData[$data[$i][$j]] = $data[$i][++$j];
                }
            }
        }

        return array_combine([$key], [$parsedData]);
    }
}
