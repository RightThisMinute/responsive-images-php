<?php

namespace ResponsiveImages;

use Functional as F;


class Source
{
  /**
   * @var Size[]
   */
  private $sizes;

  /**
   * Whether or not to render as an <img> instead of <source>. This is important
   * since the last element in a <picture> should be an <img>, but otherwise is
   * nearly the same as a <source>.
   *
   * @var bool
   */
  private $as_img;

  function __construct(array $sizes, $as_img=false)
  {
    $this->sizes  = $sizes;
    $this->as_img = $as_img;
  }

  public function renderWith($image, SrcsetGeneratorInterface $srcset_gen)
  {
    $last = F\last($this->sizes);

    $srcset = F\map($this->sizes, function(Size $size) use ($image, $srcset_gen){
      return $srcset_gen->listFor($image, $size);
    });
    $srcset = F\unique(F\flatten($srcset), function(Src $src){
      return $src->getUrl();
    });
    usort($srcset, function(Src $l, Src $r){
      $l = $l->getWidth() ?: (int)$l->getMultiplier();
      $r = $r->getWidth() ?: (int)$r->getMultiplier();
      return $l - $r;
    });
    $srcset = implode(', ', $srcset);

    $sizes  = F\map($this->sizes, function(Size $size) use ($last){
      return $size === $last ? $size->renderWidthOnly() : (string)$size;
    });
    $sizes  = implode(', ', $sizes);

    $media = !$this->as_img ? ' media="'.$last->getMediaQuery().'"' : '';

    $attributes = "srcset=\"$srcset\" sizes=\"$sizes\"$media";
    return $this->as_img ? "<img $attributes>" : "<source $attributes>";
  }
}
