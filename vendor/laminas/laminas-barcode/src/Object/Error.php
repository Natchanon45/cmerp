<?php

declare(strict_types=1);

namespace Laminas\Barcode\Object;

/**
 * Class for generate Barcode
 */
class Error extends AbstractObject
{
    /**
     * All texts are accepted
     *
     * @param string $value
     * @return bool
     */
    public function validateText($value)
    {
        return true;
    }

    /**
     * Height is forced
     *
     * @param bool $recalculate
     * @return int
     */
    public function getHeight($recalculate = false)
    {
        return 40;
    }

    /**
     * Width is forced
     *
     * @param bool $recalculate
     * @return int
     */
    public function getWidth($recalculate = false)
    {
        return 400;
    }

    /**
     * Reset precedent instructions
     * and draw the error message
     *
     * @return array
     */
    public function draw()
    {
        $this->instructions = [];
        $this->addText('ERROR:', 10, [5, 18], $this->font, 0, 'left');
        $this->addText($this->text, 10, [5, 32], $this->font, 0, 'left');
        return $this->instructions;
    }

    /**
     * For compatibility reason
     *
     * @return void
     */
    protected function prepareBarcode()
    {
    }

    /**
     * For compatibility reason
     *
     * @return void
     */
    protected function checkSpecificParams()
    {
    }

    /**
     * For compatibility reason
     *
     * @return void
     */
    protected function calculateBarcodeWidth()
    {
    }
}
