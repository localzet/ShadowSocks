<?php

/**
 * @package     Triangle Engine (FrameX Project)
 * @link        https://github.com/localzet/FrameX      FrameX Project v1-2
 * @link        https://github.com/Triangle-org/Engine  Triangle Engine v2+
 *
 * @author      Ivan Zorin <creator@localzet.com>
 * @copyright   Copyright (c) 2018-2024 Localzet Group
 * @license     https://www.gnu.org/licenses/agpl AGPL-3.0 license
 *
 *              This program is free software: you can redistribute it and/or modify
 *              it under the terms of the GNU Affero General Public License as
 *              published by the Free Software Foundation, either version 3 of the
 *              License, or (at your option) any later version.
 *
 *              This program is distributed in the hope that it will be useful,
 *              but WITHOUT ANY WARRANTY; without even the implied warranty of
 *              MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *              GNU Affero General Public License for more details.
 *
 *              You should have received a copy of the GNU Affero General Public License
 *              along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

namespace localzet\ShadowSocks;

use Exception;
use Psr\Container\{ContainerExceptionInterface, NotFoundExceptionInterface};
use function array_key_exists;
use function class_exists;

/**
 * Class Container
 * @method static mixed get($name)
 * @method static mixed make($name, array $parameters)
 * @method static bool has($name)
 */
class Container
{
    /**
     * @var array
     */
    protected array $instances = [];
    /**
     * @var array
     */
    protected array $definitions = [];

    /**
     * Находит запись контейнера по ее идентификатору и возвращает его.
     *
     * @param string $id Идентификатор записи для поиска.
     *
     * @return mixed Запись.
     * @throws ContainerExceptionInterface Ошибка при получении записи.
     *
     * @throws NotFoundExceptionInterface  Для данного идентификатора запись не найдена.
     * @throws Exception
     */
    public function _get(string $id): mixed
    {
        if (!isset($this->instances[$id])) {
            if (isset($this->definitions[$id])) {
                $this->instances[$id] = call_user_func($this->definitions[$id], $this);
            } else {
                if (!class_exists($id)) {
                    throw new Exception("Класс '$id' не найден");
                }
                $this->instances[$id] = new $id();
            }
        }
        return $this->instances[$id];
    }

    /**
     * Возвращает true, если контейнер может вернуть запись для данного идентификатора.
     *  В противном случае возвращает false.
     *
     * `has($id)`, возвращающее true, не означает, что `get($id)` не вызовет исключение.
     * Однако это означает, что `get($id)` не будет вызывать `NotFoundExceptionInterface`.
     *
     * @param string $id Идентификатор записи для поиска.
     *
     * @return bool
     */
    public function _has(string $id): bool
    {
        return array_key_exists($id, $this->instances)
            || array_key_exists($id, $this->definitions);
    }

    /**
     * Собрать
     * @param string $name
     * @param array $constructor
     * @return mixed
     * @throws Exception
     */
    public function _make(string $name, array $constructor = []): mixed
    {
        if (!class_exists($name)) {
            throw new Exception("Класс '$name' не найден");
        }
        return new $name(...array_values($constructor));
    }

    /**
     * Добавить определения
     * @param array $definitions
     * @return $this
     */
    public function _addDefinitions(array $definitions): Container
    {
        $this->definitions = array_merge($this->definitions, $definitions);
        return $this;
    }

    /**
     * @param string $name
     * @param array $arguments
     * @return mixed
     */
    public static function __callStatic(string $name, array $arguments)
    {
        return static::instance()->{'_' . $name}(...$arguments);
    }

    /**
     * @return static
     */
    public static function instance(): static
    {
        return new static();
    }
}
