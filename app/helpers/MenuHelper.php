<?php

/**
 * 菜单
 */
class Menu
{
	public static $menu = null;
    public static $subMenuUrlCache = null;
    public static $currMenuId = null;

    public static function init()
    {
        if (empty(self::$menu)) {
            self::$menu = Load::conf('Menu');
            self::$subMenuUrlCache = self::subMenuUrlCache(self::$menu);
            $curr = lcfirst(CURRENT_CONTROLLER).'/'.CURRENT_ACTION;
            $curr = str_replace('/index', '', $curr);

            $tmp = $curr;
            $curLevel = self::$subMenuUrlCache[$curr]['level'];
            if ($curLevel == 1) self::$currMenuId['level1'] = $tmp;

            for ($i = $curLevel; $i > 1; $i--) {
                $tmp = self::$subMenuUrlCache[$tmp]['parent'];
                self::$currMenuId['level'.($i-1)] = $tmp;
            }
        }
	}

    /**
     * 生成菜单
     *
     * @return string
     */
    public static function menuRender()
    {
		self::init();
		$html = '<ul class="nav navbar-nav">';
		foreach (self::$menu as $top => $topMenus) {//1级菜单
            if (!UserModel::canMenu($top)) continue;

			$active = self::$currMenuId['level1'] == $top ? 'active' : '';
			$li = '<li class="dropdown '.$active.'">';
            if (empty($topMenus['children'])) {
                $li .= sprintf('<a href="%s">%s</a>', url($top), $topMenus['name']);
            } else {
                $li .= '<a href="javascript:;" class="dropdown-toggle" data-toggle="dropdown">'.$topMenus['name'].'</a><ul class="dropdown-menu" role="menu">';
				foreach ($topMenus['children'] as $second => $secondMenus) {//2级菜单
                    if (!UserModel::canMenu($top, $second)) continue;

					if (!empty($secondMenus['children'])) {
						$li .= '<li class="offset-right dropdown"><a href="javascript:;">'.$secondMenus['name'].'</a><ul class="dropdown-menu" role="menu">';
						foreach ($secondMenus['children'] as $third => $thirdMenus) {//3级菜单
							$li .= sprintf('<li><a href="%s">%s</a></li>', url($third), $thirdMenus['name']);
						}
						$li .= '</li>';
					} else {
						$li .= sprintf('<li><a href="%s">%s</a></li>', url($second), $secondMenus['name']);
					}
				}
                $li .= '</ul>';
			}
			$li .= '</li>';
			$html .= $li;
		}
		$html .= '</ul>';

		return $html;
	}

    /**
     * 生成导航条
     *
     * @param $title
     * @param $toolBox
     * @return string
     */
    public static function navRender($title, $toolBox)
    {
		self::init();
		$html = '<ol class="breadcrumb">';
		if (isset(self::$currMenuId['level1'])) {
			$menu = self::$currMenuId['level1'];
            $curMenu = self::$menu[$menu];
			$html .= sprintf('<li><a href="%s">%s</a></li>', url($menu), $curMenu['name']).PHP_EOL;
		}
		if (isset(self::$currMenuId['level2'])) {
			$subMenu = self::$currMenuId['level2'];
            $curMenu = $curMenu['children'][$subMenu];
			$html .= sprintf('<li><a href="%s">%s</a></li>', url($subMenu), $curMenu['name']).PHP_EOL;
		}
		if (isset(self::$currMenuId['level3'])) {
			$subSubMenu = self::$currMenuId['level3'];
            $curMenu = $curMenu['children'][$subSubMenu];
			$html .= sprintf('<li><a href="%s">%s</a></li>', url($subSubMenu), $curMenu['name']).PHP_EOL;
		}
        if (!empty($title)) $html .= '<li class="active">'.$title.'</li>';
		$html .= '<span style="float:right;">'.$toolBox.'</span>';
		$html .= '</ol>';
		return $html;
	}

    public static function subMenuUrlCache($menuConf)
    {
		$cache = array();
        foreach ($menuConf as $menuId => $menu) {
            $cache[$menuId]['level'] = 1;
            if (strstr($menuId, '/index') === false) {
                $cache[$menuId.'/index']['level'] = 1;
            }
            if (!empty($menu['children'])) {
                self::cache($menuId, $menu['children'], $cache, 2);
            }
        }
        return $cache;
    }

    private static function cache($parent, $menus, &$cache, $level)
    {
        foreach ($menus as $menuId => $menu) {
            $cache[$menuId]['parent'] = $parent;
            $cache[$menuId]['level'] = $level;
            if (!empty($menu['include'])) {
                foreach ($menu['include'] as $url) {
                    $cache[$url]['parent'] = $menuId;
                    $cache[$url]['level'] = $level+1;
                }
            }
            if (!empty($menu['children'])) self::cache($menuId, $menu['children'], $cache, $level+1);
        }
    }
}
