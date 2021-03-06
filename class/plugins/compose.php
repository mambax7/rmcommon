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
 * phMagick - creating new images by joining several images or by grabbing frames
 *
 * @package    phMagick
 * @version    0.1.0
 * @author     Nuno Costa - sven@francodacosta.com
 * @copyright  Copyright (c) 2007
 * @license    http://www.francodacosta.com/phmagick/license/
 * @link       http://www.francodacosta.com/phmagick
 * @since      2008-03-13
 */
class phMagick_compose
{
    /**
     * Add's an watermark to an image
     *
     * @param phmagick        $p   phMagick
     * @param string          $watermarkImage - Image path
     * @param phMagickGravity $gravity        - phMagickGravity - the placement of the watermark
     * @param int             $transparency   - 1 to 100 the tranparency of the watermark (100 = opaque)
     * @return \phmagick
     */
    public function watermark(phmagick $p, $watermarkImage, $gravity = 'center', $transparency = 50)
    {
        //composite -gravity SouthEast watermark.png original-image.png output-image.png
        $cmd = $p->getBinary('composite');
        $cmd .= ' -dissolve ' . $transparency;
        $cmd .= ' -gravity ' . $gravity;
        $cmd .= ' ' . $watermarkImage;
        $cmd .= ' "' . $p->getSource() . '"';
        $cmd .= ' "' . $p->getDestination() . '"';

        $p->execute($cmd);
        $p->setSource($p->getDestination());
        $p->setHistory($p->getDestination());

        return $p;
    }

    /**
     * Joins severall imagens in one tab strip
     *
     * @param phmagick $p phMagick
     * @param null|array $paths array of Strings - the paths of the images to join
     * @param mixed      $tileWidth
     * @param mixed      $tileHeight
     * @return \phmagick
     */
    public function tile(phmagick $p, array $paths = null, $tileWidth = '', $tileHeight = 1)
    {
        if (null === $paths) {
            $paths = $p->getHistory(phMagickHistory::returnArray);
        }
        $cmd = $p->getBinary('montage');
        $cmd .= ' -geometry x+0+0 -tile ' . $tileWidth . 'x' . $tileHeight . ' ';
        $cmd .= implode(' ', $paths);
        $cmd .= ' "' . $p->getDestination() . '"';

        $p->execute($cmd);
        $p->setSource($p->getDestination());
        $p->setHistory($p->getDestination());

        return $p;
    }

    /**
     * Attempts to create an image(s) from a File (PDF & Avi are supported on most systems)
     * it grabs the first frame / page from the source file
     * @param phmagick $p phMagick
     * @param string $file - the path to the file
     * @param mixed  $frames
     * @return \phmagick
     */
    public function acquireFrame(phmagick $p, $file, $frames = 0)
    {
        // $cmd = 'echo "" | '; //just a workarround for videos,
        //                    imagemagick first converts all frames then deletes all but the first
        $cmd = $p->getBinary('convert');
        $cmd .= ' "' . $file . '"[' . $frames . ']';
        $cmd .= ' "' . $p->getDestination() . '"';

        $p->execute($cmd);
        $p->setSource($p->getDestination());
        $p->setHistory($p->getDestination());

        return $p;
    }
}
