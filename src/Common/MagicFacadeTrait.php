<?php

namespace Potherca\SimplyEdit\Common;

trait MagicFacadeTrait
{
    //////////////////////////// SETTERS AND GETTERS \\\\\\\\\\\\\\\\\\\\\\\\\\\
    abstract protected function getSubjectForMagicFacade();

    //////////////////////////////// PUBLIC API \\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\
    final public function __call($name, $parameters)
    {
        $subject = $this->findSubjectForMethod($name);

        return call_user_func_array([$subject, $name], $parameters);
    }

    final public function __get($name)
    {
        $subject = $this->findSubjectForProperty($name);

        return $subject->$name;
    }


    final public function __set($name, $value)
    {
        $subject = $this->findSubjectForProperty($name);

        $subject->$name = $value;
    }

    ////////////////////////////// UTILITY METHODS \\\\\\\\\\\\\\\\\\\\\\\\\\\\\
    private function findSubjectForCallback($name, $errorMessage, Callable $callback)
    {
        $subject = null;

        foreach ($this->getSubjectForMagicFacade() as $candidate) {
            if ($callback($candidate, $name) === true) {
                $subject = $candidate;
                break;
            }
        }

        if ($subject === null) {
            throw new Exception(
                // @CHECKME: Trigger a "PHP Notice" for properties to be more in line with the native API?
                // @CHECKME: Trigger a "PHP Fatal error" for methods to be more in line with the native API?
                sprintf($errorMessage, __CLASS__, $name)
            );
        } else {
            return $subject;
        }
    }

    private function findSubjectForMethod($name)
    {
        return $this->findSubjectForCallback(
            $name,
            'Call to undefined method %s::%s()',
            function ($candidate, $name) {
                return is_callable([$candidate, $name]);
            }
        );
    }

    private function findSubjectForProperty($name)
    {
        return $this->findSubjectForCallback(
            $name,
            'Undefined property: %s::$%s',
            function ($candidate, $name) {
                return property_exists($candidate, $name);
            }
        );
    }
}

/*EOF*/
