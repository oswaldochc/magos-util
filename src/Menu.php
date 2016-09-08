<?php
namespace Magos\Util;

class Menu
{
	protected $aMenu;
	public function __construct($aMenu) {
		if (is_array($aMenu)) {
			$this->aMenu = $aMenu;
		} else {
			throw new \Exception('Menu array not found');
		}
	}
	public function getMenu() {
		$aMenusTemp = $this->buildingMenu($this->aMenu);
		$this->ordenar($aMenusTemp, array('ispadre' => 'DESC', 'orden_menu' => 'ASC'));
		$data = $this->getBuildingMenuByType($aMenusTemp);
		return $data;
	}

	protected function seekMenu($aMenu,$id){
		$men = array();
		foreach($aMenu as $k => $v){
			if($v['codigo_menu'] == $id){
				$men = $aMenu[$k];
				break;
			}
		}
		return $men;
	}
	/**
	 *	Select devuelve todas los menus cabeceras
 	 *	y los menus con procesos verificar los
	 *	padres de los menus para proceder a organizar los que corresponden
	 */
	protected function buildingMenu($aMenu){
		$data = array();
		$exist = 0;
		foreach($aMenu as $k => $v){
			$menu = $v;
			if($menu['ispadre'] == 0) {
				//echo 'padre '.$menu['codigo_menu']."\n";
				$data[] = $menu;
				do{
					$codigo = 0;
					$menu = $this->seekMenu($aMenu, $menu['padre_menu']);
					if(count($menu) > 0 && is_array($menu)) {
						$codigo = $menu['codigo_menu'];
						//echo $codigo."\n";
						$exist = 0;//print_r($data);
						foreach($data as $ks => $vs){
							if($vs['codigo_menu'] == $codigo){
								$exist++;
								break;
							}
						}
						if($exist == 0) {
							$data[] = $menu;
						}
					}
				} while($codigo > 0);
			}
		}
		//print_r($data);exit;
		return $data;
	}
	protected function buildMenu($aMenu, $id = 0, &$data){
		foreach ($aMenu as $item){
			if($item['men_codigo'] == $id){
				$exist = false;
				foreach($data as $i){
					if($i['men_codigo'] == $id){
						$exist = true; break;
					}
				}
				if(!$exist){
					array_push($data, $item);
				}
				if($item['men_codigo_padre'] > 0) {
					$this->buildMenu($aMenu, $item['men_codigo_padre'], $data);
				}
				break;
			}
		}
	}
	protected function getBuildingMenuByType($aMenuItems,$id = 0)
	{
		$data = array();
		foreach ($aMenuItems as $item ){
			if($item['padre_menu'] == $id && $item['codigo_menu'] != $id){
			    $aChildren = $this->getBuildingMenuByType($aMenuItems,$item['codigo_menu']);
				$idItem = empty($item['id']) ? $item['codigo_menu'] : $item['id'];
				array_push($data,
					array(
						'text' => $item['nombre_menu'],
						'children' => $aChildren,
						'leaf' => (count($aChildren)) ? false : true,
						'url' => $item['url'],
					)
				);
			}
		}
		return $data;
	}

	protected function ordenar(&$aTabla,$aCampos) {
		$aSalida=array();
		foreach($aCampos as $sCampo=>$sOrden) {
			if($sOrden=='ASC') {
				$s1='>';
				$s2='<';
			} else {
				$s1='<';
				$s2='>';
			}
			$aSalida[]="
			if(array_key_exists('$sCampo',\$a)) {
				if(\$a['$sCampo'] $s1 \$b['$sCampo']) {
					return 1;
				} elseif (\$a['$sCampo'] $s2 \$b['$sCampo']) {
					return -1;
				}
			}
			";
		}
		$aSalida[]='return 0;';
		uasort($aTabla, create_function('$a, $b', implode("\n",$aSalida)));
	}
}
