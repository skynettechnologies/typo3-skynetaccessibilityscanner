<?php
namespace Skynettechnologies\Skynetaccessibilityscanner\Controller;

/**
 * Class ConstantClass
 * This class handles the initialization and management of constants for the accessibility tool.
 */
class ConstantClass
{
    /**
     * @var object
     */
    protected $pObj;

    /**
     * Initializes the constants, you can modify the logic based on your needs.
     *
     * @param object $pObj
     */
    public function init($pObj): void
    {
        // Initialize the pObj or other necessary properties
        $this->pObj = $pObj;

        // Example: You can set some constants here
        define('MY_CONSTANT', 'Some value');
    }

    /**
     * This method will return constants or any other value you need to process.
     *
     * @return array
     */
    public function main(): array
    {
        // You can return an array of constants or other data
        return [
            'MY_CONSTANT' => MY_CONSTANT
        ];
    }
}
