<?php
require_once('manual-init.php');
use SyHolloway\MrColor\Color;

class Sedo_ColorsHelper_Helper_PlayWithColors
{
	public static function init($color, $cmd = null, $option = null, $debug = false)
	{
		$color =str_replace(' ', '', $color);
		$option = str_replace(' ', '', $option);
		$cmd = strtolower(str_replace(' ', '', $cmd));

		$color = self::_checkIfColorName($color);

		//Start Mr Color
		$color = Color::load($color);

		switch($cmd)
		{
			case '_hex': return $color->hex;
			case '_red': return $color->red;
		        case '_green': return $color->green;
		        case '_blue': return $color->blue;
		        case '_hue': return $color->hue;
		        case '_saturation': return $color->saturation;
		        case '_lightness': return $color->lightness;
		        case '_alpha': return $color->alpha;
			case 'isLight': return $color->isLight();
			case 'isDark':	return $color->isDark();
			case 'darken': return $color->darken($option);
			case 'lighten': return $color->lighten($option);
			case 'hex': return $color->getHexString();
			case 'rgb': return $color->getRgbString();
			case 'rgba': return $color->getRgbaString();
			case 'hsl': return $color->getHslString();
			case 'hsla': return $color->getHslaString();
			case 'argb': return $color->getArgbHexString();
			case 'gradient': return self::_createCssGradient($color, $option);
			case 'calc': return self::_calc2hex($color, $option);
			case 'modify': $output = self::_modify($color, $option);break;
			default: $output = self::_fullOutput($color);
		}

		if($debug == true && is_array($output))
		{
			$string = '';
			foreach($output as $k => $v)
			{
				$string.="[$k:$v] ";
			}
			
			$output = $string;
		}
		
		return $output;
	}
	
	protected static function _modify($color, $option)
	{
		$option = strtolower(str_replace(' ', '', $option));
		$options = explode(';', $option);
		$validCmd = array('red', 'green', 'blue', 'hue', 'saturation', 'lightness', 'alpha');
		$validOutput = array('_hex', 'hex', 'rgb', 'rgba', 'hsl', 'hsla', 'aarrggbb');
		$output = null;

		foreach($options as $data)
		{
			/* Check if an output has been set */
			if(in_array($data, $validOutput))
			{
				$output = $data;
				continue;
			}

			/* Search for ":" */		
			$pos = strpos($data, ':');

			if($pos === false)
			{
				continue;
			}
			
			/* Search for command */
			$cmd = substr($data, 0, $pos);

			$key = array_search($cmd, $validCmd);
			if( $key === false)
			{
				continue;
			}

			if(!isset($data[$pos+1]))
			{
				continue;
			}

			/* Manage operator & value */
			if(in_array($data[$pos+1], array('+', '-')))
			{
				$operator = $data[$pos+1];
				$value = (isset($data[$pos+2])) ? substr($data, $pos+2) : 1;
			}
			else
			{
				$operator = null;
				$value = substr($data, $pos+1);			
			}

			/* Proceed */
			switch($operator)
			{
				case '+':
					if($color->$cmd + $value > 255)
					{
						$color->$cmd = 255;
					}
					else
					{
						$color->$cmd += $value;					
					}
					continue;
				case '-':
					if($color->$cmd - $value < 0)
					{
						$color->$cm = 0;
					}
					else
					{
						$color->$cmd -= $value;					
					}
					continue;
				default:
					$color->$cmd = $value;
			}
		}

		if($output == '_hex')
		{
			return $color->hex;
		}
		elseif($output)
		{
			return $color->$output();
		}
		else
		{
			return self::_fullOutput($color);
		
		}
	}	

	protected static function _fullOutput($color)
	{
		return array(
			'_hex' => $color->hex,
			'_red' => $color->red,
			'_green' => $color->green,
			'_blue' => $color->blue,
			'_hue' => $color->hue,
			'_saturation' => $color->saturation,
			'_lightness' => $color->lightness,
			'_alpha' => $color->alpha,
			'isLight' => $color->isLight(),
			'isDark' => $color->isDark(),
			'darken' => $color->darken($option),
			'lighten' => $color->lighten($option),
			'hex' => $color->getHexString(),
			'rgb' => $color->getRgbString(),
			'rgba' => $color->getRgbaString(),
			'hsl' => $color->getHslString(),
			'hsla' => $color->getHslaString(),
			'argb' => $color->getArgbHexString()
		);	
	}
	
	protected static function _createCssGradient($color, $option)
	{
		$fallback = $color->getRgbaString();
		$mode = (preg_match('#\d{1,3}#', $option)) ? 'singleColor' : 'dualColor';
		
		if($mode == 'singleColor')
		{
			$amount = (int)$option;
			
			if ($color->isLight())
		        {
        			$lightColor = $color->getRgbaString();
        			$lightColorIE = $color->getArgbHexString();
				
				$color->darken($amount);
				$darkColorIE = $color->getArgbHexString();
				$darkColor = $color->getRgbaString();
			}
			else
			{
				$darkColor = $color->getRgbaString();
				$darkColorIE = $color->getArgbHexString();
				
				$color->lighten($amount);
        			$lightColorIE = $color->getArgbHexString();
				$lightColor = $color->getRgbaString();
			}
	        }
	        else
	        {
	        	$firstColor = $color->copy();
			$secondColor = $option;

       			$lightColor = $firstColor->getRgbaString();
       			$lightColorIE = $firstColor->getArgbHexString();

			$secondColor = self::_checkIfColorName($option);
			$secondColor = Color::load($secondColor);
			$darkColor = $secondColor->getRgbaString();
			$darkColorIE = $secondColor->getArgbHexString();
		}

		$css = "background-color:{$fallback};\n";
		$css.= "background-image:-moz-linear-gradient(top,{$lightColor},{$darkColor});\n";
		$css.= "background-image:-webkit-gradient(linear,0 0,0 100%,from({$lightColor}),to({$darkColor}));\n";
		$css.= "background-image:-webkit-linear-gradient(top,{$lightColor},{$darkColor});\n";
	        $css.= "background-image:-o-linear-gradient(top,{$lightColor},{$darkColor});\n";
	        $css.= "background-image:linear-gradient(to bottom,{$lightColor},{$darkColor});\n";	        
	        $css.= "filter:progid:DXImageTransform.Microsoft.gradient(startColorstr='{$lightColorIE}',endColorstr='{$darkColorIE}',GradientType=0);\n";
	    	$css.= "background-repeat:repeat-x;\n";

		return $css;	        
	}
	
	protected static function _calc2hex($color, $option)
	{
		$color1 = $color->hex;

		$operator = '+';
		if(in_array($option[0], array('+', '-')))
		{
			$operator = $option[0];
			$option = substr($option, 1);
		}

		$color2 = self::_checkIfColorName($option);
		$color2 = Color::load($color2);
		$color2 = $color2->hex;

		switch($operator)
		{
			case '-': $newColor = dechex(hexdec($color) - hexdec($color2)); break;
			default:
				$newColor = dechex(hexdec($color) + hexdec($color2)); break;	
		}
		
		return "#{$newColor}";
	}
	
	protected static function _checkIfColorName($color)
	{
		$colorNames = XenForo_Helper_Color::$colors;
		if(isset($colorNames[$color]))
		{
			$color = $colorNames[$color];
		}
		
		return $color;
	}
}