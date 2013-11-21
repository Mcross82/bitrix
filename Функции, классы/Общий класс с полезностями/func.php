<?
class FUNC{
	// ресайз фото. ID фото в битриксе, макс ширина, макс высота, возвр. ли размеры после ресайза
	public static function getResizeImg($idImg,$width=220,$height=220,$width_size=false){
		if($width_size){
			if($renderImage=CFile::ResizeImageGet($idImg,array("width"=>$width,"height"=>$height),BX_RESIZE_IMAGE_PROPORTIONAL,true)){
				return $renderImage;	
			}
			return false;
		}else{
			if($renderImage=CFile::ResizeImageGet($idImg,array("width"=>$width,"height"=>$height))){
				return $renderImage['src'];
			}
			return false;
		}
	}

	// получить кол-во товара в корзине
	static public function getCountProductsBasket(){
		CModule::IncludeModule('sale');
		$totalProduct=0;
		$dbBasketItems=CSaleBasket::GetList(
			array(),array(
				"FUSER_ID" => CSaleBasket::GetBasketUserID(),
				"LID" => SITE_ID,
				"ORDER_ID" => "NULL"
			),false,false,array()
		);
		while($arItems=$dbBasketItems->GetNext()){
			$totalProduct+=$arItems['QUANTITY'];
		}
		return $totalProduct;
	}

	// получить разделы
	static public function getSubSections($iblockID=0,$sect_id=0,$active='Y',$arSort=array('SORT'=>'ASC'),$arUF=array()){
		if($iblockID){
			$res=CIBlockSection::GetList($arSort,array('SECTION_ID'=>$sect_id,'IBLOCK_ID'=>$iblockID,'ACTIVE'=>$active),false,$arUF);
			while($arSect=$res->GetNext()){
				$img='/i/noPhoto.png';// nophoto
				if($arSect['PICTURE']){
					$img=CFile::GetPath($arSect['PICTURE']);
				}
				$arElems[]=array(
					'name'=>$arSect['NAME'],
					'id'=>$arSect['ID'],
					'url'=>$arSect['SECTION_PAGE_URL'],
					'img'=>$img,
					);
			}
			return $arElems;
		}
		return false;
	}

	// получить содержимое корзины
	public static function getBasket(){
		CModule::IncludeModule('sale');
		//CModule::IncludeModule('iblock');
		$dbBasketItems=CSaleBasket::GetList(
			array(),array(
				"FUSER_ID" => CSaleBasket::GetBasketUserID(),
				"LID" => SITE_ID,
				"ORDER_ID" => "NULL"
			),false,false,array()
		);
		$basket=array();
		$totalProduct=$totalSumm=0;
		while($arItems=$dbBasketItems->GetNext()){
			$res=CIBlockElement::GetByID($arItems['PRODUCT_ID']);
			$arElem=$res->GetNext();
			$img='/i/noPhoto.png';
			if($arElem['PREVIEW_PICTURE']){
				$img=CFile::GetPath($arElem['PREVIEW_PICTURE']);
			}
			$basket[]=array(
				'id'=>$arItems['PRODUCT_ID'],
				'name'=>$arElem['NAME'],
				'url'=>$arElem['DETAIL_PAGE_URL'],
				'img'=>$img,
				'desc'=>htmlspecialcharsBack($arElem['PREVIEW_TEXT']),
				'price'=>number_format($arItems['PRICE'],2,'.',' '),
				'num_price'=>$arItems['PRICE'],
				'count'=>(int)$arItems['QUANTITY'],
				'sum'=>$arItems['QUANTITY']*$arItems['PRICE'],
				'idBasket'=>$arItems['ID'],
			);
			$totalProduct+=(int)$arItems['QUANTITY'];
			$totalSumm+=$arItems['PRICE']*$arItems['QUANTITY'];
		}
		return array('arBasket'=>$basket,'totalProduct'=>$totalProduct,'totalSumm'=>$totalSumm);
	}

	// проверить есть ли товар в корзине
	public static function checkProductToCart($id){
		CModule::IncludeModule('sale');
		if($id){
			$dbBasketItems = CSaleBasket::GetList(array(),
				array(
					"FUSER_ID" => CSaleBasket::GetBasketUserID(),
					"LID" => SITE_ID,
					"ORDER_ID" => "NULL",
					"PRODUCT_ID"=>$id,
				),false,false,array()
			);
			if($dbBasketItems->SelectedRowsCount()){
				return true;
			}
		}
		return false;
	}

	// получить уникальный символьный код для разделов
	public static function getNewCodeSection($IBLOCK_ID,$name){
		if($IBLOCK_ID&&$name){
			$n='';
			do{
				$code=Translit::UrlTranslit($name.$n);
				$res=CIBlockSection::GetList(array(),array('IBLOCK_ID'=>$IBLOCK_ID,'CODE'=>$code),false);
				$n++;
			}while($res->SelectedRowsCount());
			return $code;
		}
		return false;
	}
	// получить уникальный символьный код для элементов
	public static function getNewCodeElement($IBLOCK_ID,$name){
		if($IBLOCK_ID&&$name){
			$n='';
			do{
				$code=Translit::UrlTranslit($name.$n);
				$res=CIBlockElement::GetList(array(),array('IBLOCK_ID'=>$IBLOCK_ID,'CODE'=>$code),false,false,array());
				$n++;
			}while($res->SelectedRowsCount());
			return $code;
		}
		return false;
	}

	// Функция определения мобильных браузеров
	public static function is_mobile(){
		$user_agent=strtolower(getenv('HTTP_USER_AGENT'));
		$accept=strtolower(getenv('HTTP_ACCEPT'));
	 
		if ((strpos($accept,'text/vnd.wap.wml')!==false) || (strpos($accept,'application/vnd.wap.xhtml+xml')!==false)){
			return 1; // Мобильный браузер обнаружен по HTTP-заголовкам
		}
	 
		if (isset($_SERVER['HTTP_X_WAP_PROFILE']) || isset($_SERVER['HTTP_PROFILE'])){
			return 2; // Мобильный браузер обнаружен по установкам сервера
		}
	 
		if (preg_match('/(mini 9.5|vx1000|lge |m800|e860|u940|ux840|compal|'.
		'wireless| mobi|ahong|lg380|lgku|lgu900|lg210|lg47|lg920|lg840|'.
		'lg370|sam-r|mg50|s55|g83|t66|vx400|mk99|d615|d763|el370|sl900|'.
		'mp500|samu3|samu4|vx10|xda_|samu5|samu6|samu7|samu9|a615|b832|'.
		'm881|s920|n210|s700|c-810|_h797|mob-x|sk16d|848b|mowser|s580|'.
		'r800|471x|v120|rim8|c500foma:|160x|x160|480x|x640|t503|w839|'.
		'i250|sprint|w398samr810|m5252|c7100|mt126|x225|s5330|s820|'.
		'htil-g1|fly v71|s302|-x113|novarra|k610i|-three|8325rc|8352rc|'.
		'sanyo|vx54|c888|nx250|n120|mtk |c5588|s710|t880|c5005|i;458x|'.
		'p404i|s210|c5100|teleca|s940|c500|s590|foma|samsu|vx8|vx9|a1000|'.
		'_mms|myx|a700|gu1100|bc831|e300|ems100|me701|me702m-three|sd588|'.
		's800|8325rc|ac831|mw200|brew |d88|htc\/|htc_touch|355x|m50|km100|'.
		'd736|p-9521|telco|sl74|ktouch|m4u\/|me702|8325rc|kddi|phone|lg |'.
		'sonyericsson|samsung|240x|x320vx10|nokia|sony cmd|motorola|'.
		'up.browser|up.link|mmp|symbian|smartphone|midp|wap|vodafone|o2|'.
		'pocket|kindle|mobile|psp|treo)/', $user_agent)){
			return 3; // Мобильный браузер обнаружен по сигнатуре User Agent
		}
	 
		if (in_array(substr($user_agent,0,4),
	    Array("1207", "3gso", "4thp", "501i", "502i", "503i", "504i", "505i", "506i",
			"6310", "6590", "770s", "802s", "a wa", "abac", "acer", "acoo", "acs-",
			"aiko", "airn", "alav", "alca", "alco", "amoi", "anex", "anny", "anyw",
			"aptu", "arch", "argo", "aste", "asus", "attw", "au-m", "audi", "aur ",
			"aus ", "avan", "beck", "bell", "benq", "bilb", "bird", "blac", "blaz",
			"brew", "brvw", "bumb", "bw-n", "bw-u", "c55/", "capi", "ccwa", "cdm-",
			"cell", "chtm", "cldc", "cmd-", "cond", "craw", "dait", "dall", "dang",
			"dbte", "dc-s", "devi", "dica", "dmob", "doco", "dopo", "ds-d", "ds12",
			"el49", "elai", "eml2", "emul", "eric", "erk0", "esl8", "ez40", "ez60",
			"ez70", "ezos", "ezwa", "ezze", "fake", "fetc", "fly-", "fly_", "g-mo",
			"g1 u", "g560", "gene", "gf-5", "go.w", "good", "grad", "grun", "haie",
			"hcit", "hd-m", "hd-p", "hd-t", "hei-", "hiba", "hipt", "hita", "hp i",
			"hpip", "hs-c", "htc ", "htc-", "htc_", "htca", "htcg", "htcp", "htcs",
			"htct", "http", "huaw", "hutc", "i-20", "i-go", "i-ma", "i230", "iac",
			"iac-", "iac/", "ibro", "idea", "ig01", "ikom", "im1k", "inno", "ipaq",
			"iris", "jata", "java", "jbro", "jemu", "jigs", "kddi", "keji", "kgt",
			"kgt/", "klon", "kpt ", "kwc-", "kyoc", "kyok", "leno", "lexi", "lg g",
			"lg-a", "lg-b", "lg-c", "lg-d", "lg-f", "lg-g", "lg-k", "lg-l", "lg-m",
			"lg-o", "lg-p", "lg-s", "lg-t", "lg-u", "lg-w", "lg/k", "lg/l", "lg/u",
			"lg50", "lg54", "lge-", "lge/", "libw", "lynx", "m-cr", "m1-w", "m3ga",
			"m50/", "mate", "maui", "maxo", "mc01", "mc21", "mcca", "medi", "merc",
			"meri", "midp", "mio8", "mioa", "mits", "mmef", "mo01", "mo02", "mobi",
			"mode", "modo", "mot ", "mot-", "moto", "motv", "mozz", "mt50", "mtp1",
			"mtv ", "mwbp", "mywa", "n100", "n101", "n102", "n202", "n203", "n300",
			"n302", "n500", "n502", "n505", "n700", "n701", "n710", "nec-", "nem-",
			"neon", "netf", "newg", "newt", "nok6", "noki", "nzph", "o2 x", "o2-x",
			"o2im", "opti", "opwv", "oran", "owg1", "p800", "palm", "pana", "pand",
			"pant", "pdxg", "pg-1", "pg-2", "pg-3", "pg-6", "pg-8", "pg-c", "pg13",
			"phil", "pire", "play", "pluc", "pn-2", "pock", "port", "pose", "prox",
			"psio", "pt-g", "qa-a", "qc-2", "qc-3", "qc-5", "qc-7", "qc07", "qc12",
			"qc21", "qc32", "qc60", "qci-", "qtek", "qwap", "r380", "r600", "raks",
			"rim9", "rove", "rozo", "s55/", "sage", "sama", "samm", "sams", "sany",
			"sava", "sc01", "sch-", "scoo", "scp-", "sdk/", "se47", "sec-", "sec0",
			"sec1", "semc", "send", "seri", "sgh-", "shar", "sie-", "siem", "sk-0",
			"sl45", "slid", "smal", "smar", "smb3", "smit", "smt5", "soft", "sony",
			"sp01", "sph-", "spv ", "spv-", "sy01", "symb", "t-mo", "t218", "t250",
			"t600", "t610", "t618", "tagt", "talk", "tcl-", "tdg-", "teli", "telm",
			"tim-", "topl", "tosh", "treo", "ts70", "tsm-", "tsm3", "tsm5", "tx-9",
			"up.b", "upg1", "upsi", "utst", "v400", "v750", "veri", "virg", "vite",
			"vk-v", "vk40", "vk50", "vk52", "vk53", "vm40", "voda", "vulc", "vx52",
			"vx53", "vx60", "vx61", "vx70", "vx80", "vx81", "vx83", "vx85", "vx98",
			"w3c ", "w3c-", "wap-", "wapa", "wapi", "wapj", "wapm", "wapp", "wapr",
			"waps", "wapt", "wapu", "wapv", "wapy", "webc", "whit", "wig ", "winc",
			"winw", "wmlb", "wonu", "x700", "xda-", "xda2", "xdag", "yas-", "your",
			"zeto", "zte-"))) {
	    	return 4; // Мобильный браузер обнаружен по сигнатуре User Agent
		}
	 
		return false; // Мобильный браузер не обнаружен
	}

}

class Translit{
    public static function Transliterate($string){
      $cyr=array(
         "Щ",  "Ш", "Ч", "Ц","Ю", "Я", "Ж", "А","Б","В","Г","Д","Е","Ё","З","И","Й","К","Л","М","Н","О","П","Р","С","Т","У","Ф","Х", "Ь","Ы","Ъ","Э","Є","Ї",
         "щ",  "ш", "ч", "ц","ю", "я", "ж", "а","б","в","г","д","е","ё","з","и","й","к","л","м","н","о","п","р","с","т","у","ф","х", "ь","ы","ъ","э","є","ї"
      );
      $lat=array(
         "Shh","Sh","Ch","C","Ju","Ja","Zh","A","B","V","G","D","Je","Jo","Z","I","J","K","L","M","N","O","P","R","S","T","U","F","Kh","'","Y","`","E","Je","Ji",
         "shh","sh","ch","c","ju","ja","zh","a","b","v","g","d","je","jo","z","i","j","k","l","m","n","o","p","r","s","t","u","f","kh","'","y","`","e","je","ji"
      );
      for($i=0; $i<count($cyr); $i++)
      {
         $c_cyr = $cyr[$i];
         $c_lat = $lat[$i];
         $string = str_replace($c_cyr, $c_lat, $string);
      }
      $string = preg_replace("/([qwrtpsdfghklzxcvbnmQWRTPSDFGHKLZXCVBNM]+)[jJ]e/", "\${1}e", $string);
      $string = preg_replace("/([qwrtpsdfghklzxcvbnmQWRTPSDFGHKLZXCVBNM]+)[jJ]/", "\${1}'", $string);
      $string = preg_replace("/([eyuioaEYUIOA]+)[Kk]h/", "\${1}h", $string);
      $string = preg_replace("/^kh/", "h", $string);
      $string = preg_replace("/^Kh/", "H", $string);
      return $string;
   }

   public static function UrlTranslit($string){
      $string = preg_replace("/[_\s\.,?!\[\](){}]+/", "_", $string);
      $string = preg_replace("/-{2,}/", "--", $string);
      $string = preg_replace("/_-+_/", "--", $string);
      $string = preg_replace("/[_\-]+$/", "", $string);
      $string = Translit::Transliterate($string);
      $string = ToLower($string);
      $string = preg_replace("/j{2,}/", "j", $string);
      $string = preg_replace("/[^0-9a-z_\-]+/", "", $string);
      return $string;
   }
}