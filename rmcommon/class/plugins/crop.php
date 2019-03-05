<?php
/*
    +--------------------------------------------------------------------------------------------+
    |   DISCLAIMER - LEGAL NOTICE -                                                              |
    +--------------------------------------------------------------------------------------------+
    |                                                                                            |
    |  This program is free for non comercial use, see the license terms available at            |
    |  http://www.francodacosta.com/licencing/ for more information                              |
    |                                                                                            |
    |  This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; |
    |  without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. |
    |                                                                                            |
    |  USE IT AT YOUR OWN RISK                                                                   |
    |                                                                                            |
    |                                                                                            |
    +--------------------------------------------------------------------------------------------+

*/

/**
 * phMagick - Crop function
 *
 * @package    phMagick
 * @version    0.1.0
 * @author     Nuno Costa - sven@francodacosta.com
 * @copyright  Copyright (c) 2007
 * @license    http://www.francodacosta.com/phmagick/license/
 * @link       http://www.francodacosta.com/phmagick
 * @since      2008-03-13
 */
class phMagick_crop
{
    /**
     * @param phmagick        $p       phMagick
     * @param Integer         $width
     * @param Integer         $height
     * @param Integer         $top     - The Y coordinate for the left corner of the crop rectangule
     * @param Integer         $left    - The X coordinate for the left corner of the crop rectangule
     * @param phMagickGravity $gravity - The initial placement of the crop rectangule
     * @return mixed
     */
    public function crop(phmagick $p, $width, $height, $top = 0, $left = 0, $gravity = 'center')
    {
        $cmd = $p->getBinary('convert');
        $cmd .= ' ' . $p->getSource();

        if (('' != $gravity) || (phMagickGravity::None != $gravity)) {
            $cmd .= ' -gravity ' . $gravity;
        }

        $cmd .= ' -crop ' . (int)$width . 'x' . (int)$height;
        $cmd .= '+' . $left . '+' . $top;
        $cmd .= ' ' . $p->getDestination();

        $p->execute($cmd);
        $p->setSource($p->getDestination());
        $p->setHistory($p->getDestination());

        return $p;
    }
}
