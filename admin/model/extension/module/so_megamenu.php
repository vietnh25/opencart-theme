<?php

class ModelExtensionModuleSomegamenu extends Model {
    private $errors = array();
    public function generate_nestable_list($lang_id) {
		$module_id = (isset($this->request->get['module_id']) && $this->request->get['module_id']) ? $this->request->get['module_id'] : 0;
        $query = $this->db->query("SELECT * FROM ".DB_PREFIX."mega_menu WHERE parent_id='0' AND module_id='".$module_id."' ORDER BY rang");
        if (!isset($this->request->get['module_id'])) {
            $action = $this->url->link('extension/module/so_megamenu', 'user_token=' . $this->session->data['user_token'], 'SSL');
        } else {
            $action = $this->url->link('extension/module/so_megamenu', 'user_token=' . $this->session->data['user_token'] . '&module_id=' . $this->request->get['module_id'], 'SSL');
        }
        $output = '<div class="cf nestable-lists">';
        $output .= '<div class="dd" id="nestable">';
        $output .= '<ol class="dd-list">';
        foreach ($query->rows as $row) {
			$prId = $row['id'];
			if($row['id_old'] != 0 ) {
				$prId = $row['id_old'];
			}
			$json = unserialize($row['name']);
            if(isset($json[$lang_id])) {
                $name = $this->skrut($json[$lang_id], 10);
            } else {
                $name = 'Set name';
            }
            if ($row['status']==0)
                $class ='fa fa-square';
            else
                $class ='fa fa-square-o';
            $output .= '<li class="dd-item" data-id="'.$row['id'].'">';
            $output .= '<a data-toggle="tooltip" title="Duplicate" href="'.$action.'&duplicate='.$row['id'].'"  class="fa fa-plus"></a>';
            $output .= '<a data-toggle="tooltip" title="Change Status" href="'.$action.'&changestatus='.$row['id'].'"  class="'.$class.'"></a>';
            $output .= '<a data-toggle="tooltip" title="Delete" href="'.$action.'&delete='.$row['id'].'" onclick="return confirm(\'Are you sure you want to delete?\')" class="fa fa-trash-o fa-fw"></a><a data-toggle="tooltip" title="edit" href="'.$action.'&edit='.$row['id'].'" class="fa fa-pencil fa-fw"></a>';
            $output .= '<div class="dd-handle">'.$name.' (ID: '.$row['id'].')</div>';
            $output .= $this->menu_showNested( $prId , $lang_id);
			$output .= $this->menu_reset_id($row['id']);
            $output .= '</li>';
        }
        $output .= '</ol>';
        $output .= '</div>';
        $output .= '</div>';	
        return $output;
    }
	
	public function menu_reset_id($id) {
        $this->db->query("UPDATE " . DB_PREFIX . "mega_menu SET id_old = 0  WHERE id = '" . $id . "'");		
	}

    public function menu_showNested($parentID, $lang_id) {
        $query = $this->db->query("SELECT * FROM ".DB_PREFIX."mega_menu WHERE parent_id='".$parentID."' ORDER BY rang");

        if (!isset($this->request->get['module_id'])) {
            $action = $this->url->link('extension/module/so_megamenu', 'user_token=' . $this->session->data['user_token'], 'SSL');
        } else {
            $action = $this->url->link('extension/module/so_megamenu', 'user_token=' . $this->session->data['user_token'] . '&module_id=' . $this->request->get['module_id'], 'SSL');
        }
        $output = false;
        if (count($query->rows) > 0) {
            $output .= "<ol class='dd-list'>\n";
            foreach ($query->rows as $row) {
                $output .= "\n";
				$json = unserialize($row['name']);
                if(isset($json[$lang_id])) {
                    $name = $this->skrut($json[$lang_id], 10);
                } else {
                    $name = 'Set name';
                }
                if ($row['status']==0)
                    $class ='fa fa-square';
                else
                    $class ='fa fa-square-o';
                $output .= "<li class='dd-item' data-id='{$row['id']}'>\n";
                $output .= '<a data-toggle="tooltip" title="Duplicate" href="'.$action.'&duplicate='.$row['id'].'"  class="fa fa-plus"></a>';
                $output .= '<a data-toggle="tooltip" title="Change Status" href="'.$action.'&changestatus='.$row['id'].'" class="'.$class.'" ></a>';
                $output .= '<a  data-toggle="tooltip" title="Delete" href="'.$action.'&delete='.$row['id'].'" onclick="return confirm(\'Are you sure you want to delete?\')" class="fa fa-trash-o fa-fw"></a>';
                $output .= "<a data-toggle='tooltip' title='edit'  href='".$action."&edit=".$row['id']."' class='fa fa-pencil fa-fw'></a><div class='dd-handle'>{$name} (ID: {$row['id']})</div>\n";
                $output .= $this->menu_showNested($row['id'], $lang_id);
                $output .= "</li>\n";
            }
            $output .= "</ol>\n";
        }
        return $output;
    }

    public  function getSubMenu($parentID){
        $query = $this->db->query("SELECT * FROM ".DB_PREFIX."mega_menu WHERE parent_id='".$parentID."' ORDER BY rang");
        return $query->rows;
    }
    public function save_rang($parent_id, $id, $rang) {
        $this->db->query("UPDATE " . DB_PREFIX . "mega_menu SET parent_id = '" . $parent_id . "', rang = '" . $rang . "' WHERE id = '" . $id . "'");
    }



    public function addMenu($data) {
						
		$data['parent_id'] = (isset($data['parent_id']) && $data['parent_id']) ? $data['parent_id'] : 0;
		
		if(isset($data['module_id']) && $data['module_id'])
			$module_id = $data['module_id'];
		else
			$module_id = (isset($this->request->get['module_id']) && $this->request->get['module_id']) ? $this->request->get['module_id'] : 0;		
        //$data['content']['categories']['categories'] = @json_decode(html_entity_decode($data['content']['categories']['categories']), true);					
	    if(isset($data['module_id'])) {			
			if($data['parent_id'] === 0 ) {
				
                $this->db->query("INSERT INTO " . DB_PREFIX . "mega_menu SET id_old = '" . $data['id'] .strftime('%H%M') . "' , name = '" . $data['name'] . "',label_item = '".$data['label_item']."',icon_font = '".$data['icon_font']."',class_menu = '".$data['class_menu']."',  description = '" . $data['description'] . "', icon = '" . $data['icon'] . "', parent_id = '". $data['parent_id'] ."', type_link = '" . $data['type_link'] . "', module_id = '" . $module_id . "', link = '" . $data['link'] . "', new_window = '" . $data['new_window'] . "', status = '" . $data['status'] . "', position = '" . $data['position'] . "', submenu_width = '" . $data['submenu_width'] . "', submenu_type = '" . $data['display_submenu'] . "', rang='".$data['rang']."', content_width='" . $data['content_width'] . "', content_type='" . $data['content_type'] . "',  content='" . $this->db->escape(($data['content'])) . "'");	
			}
			else {
                $this->db->query("INSERT INTO " . DB_PREFIX . "mega_menu SET name = '" . $data['name'] . "',label_item = '".$data['label_item']."',icon_font = '".$data['icon_font']."',class_menu = '".$data['class_menu']."',  description = '" . $data['description'] . "', icon = '" . $data['icon'] . "', parent_id = '". $data['parent_id'] .strftime('%H%M') . "', type_link = '" . $data['type_link'] . "', module_id = '" . $module_id . "', link = '" . $data['link'] . "', new_window = '" . $data['new_window'] . "', status = '" . $data['status'] . "', position = '" . $data['position'] . "', submenu_width = '" . $data['submenu_width'] . "', submenu_type = '" . $data['display_submenu'] . "', rang='".$data['rang']."', content_width='" . $data['content_width'] . "', content_type='" . $data['content_type'] . "',  content='" . $this->db->escape(($data['content'])) . "'");
			}
			
		}else {
                $this->db->query("INSERT INTO " . DB_PREFIX . "mega_menu SET name = '" . $this->db->escape(serialize($data['name'])) . "',label_item = '".$data['label_item']."',icon_font = '".$data['icon_font']."',class_menu = '".$data['class_menu']."',  description = '" . $this->db->escape(serialize($data['description'])) . "', icon = '" . $data['icon'] . "', parent_id = '". $data['parent_id'] ."', type_link = '" . $data['type_link'] . "', module_id = '" . $module_id . "', link = '" . $data['link'] . "', new_window = '" . $data['new_window'] . "', status = '" . $data['status'] . "', position = '" . $data['position'] . "', submenu_width = '" . $data['submenu_width'] . "', submenu_type = '" . $data['display_submenu'] . "', rang='1000', content_width='" . $data['content_width'] . "', content_type='" . $data['content_type'] . "', content='" . $this->db->escape(serialize($data['content'])) . "'");
			
		}

		
        return $this->db->getLastId();
    }

    public function saveMenu($data) {		
		
		$module_id = (isset($this->request->get['module_id']) && $this->request->get['module_id']) ? $this->request->get['module_id'] : 0;
        $data['content']['categories']['categories'] = json_decode(html_entity_decode($data['content']['categories']['categories']), true);
        $this->db->query("UPDATE " . DB_PREFIX . "mega_menu SET name = '" . $this->db->escape(serialize($data['name'])) . "', label_item = '".$data['label_item']."',icon_font = '".$data['icon_font']."',class_menu = '".$data['class_menu']."', description = '" . $this->db->escape(serialize($data['description'])) . "', icon = '" . $data['icon'] ."',type_link = '" . $data['type_link'] . "', module_id = '" . $module_id . "', link = '" . $data['link'] ."', new_window = '" . $data['new_window'] ."', status = '" . $data['status'] ."', position = '" . $data['position'] ."', submenu_width = '" . $data['submenu_width'] ."', submenu_type = '" . $data['display_submenu'] ."', content_width = '" . $data['content_width'] ."', content_type = '" . $data['content_type'] ."', content = '" . $this->db->escape(serialize($data['content'])) . "' WHERE id = '" . $data['id'] . "'");
    }
    public function UpdatePosition($data) {
        $this->db->query("UPDATE " . DB_PREFIX . "mega_menu SET  status = '" . $data['status'] ."' WHERE id = '" . $data['id'] . "'");
    }

    public function deleteMenu($id) {
        $query = $this->db->query("SELECT * FROM ".DB_PREFIX."mega_menu WHERE id='".$id."'");
        if(count($query->rows) > 0) {
            $query = $this->db->query("SELECT * FROM ".DB_PREFIX."mega_menu WHERE parent_id='".$id."'");
            if(count($query->rows) > 0) {
                $this->errors[] = "Menu wasn't removed because contains submenu.";
            } else {
                $this->db->query("DELETE FROM " . DB_PREFIX . "mega_menu WHERE id = '" . $id . "'");
                return true;
            }
        } else {
            $this->errors[] = 'This menu does not exist!';
        }
        return false;
    }
    public function deleteAllMenu($id) {
        $query = $this->db->query("SELECT * FROM ".DB_PREFIX."mega_menu WHERE id='".$id."'");
        if(count($query->rows) > 0) {
            $this->db->query("DELETE FROM " . DB_PREFIX . "mega_menu WHERE id = '" . $id . "'");
        } else {
            $this->errors[] = 'This menu does not exist!';
        }
        return false;
    }

    public function getMenu($id) {
		
        $query = $this->db->query("SELECT * FROM ".DB_PREFIX."mega_menu WHERE id='".$id."'");
        if(count($query->rows) > 0) {
            $data = array();
			
            foreach ($query->rows as $result) {
				$check = unserialize($result['content']);
				
			
                $data = array(
                    'name' => unserialize($result['name']),
                    'description' => unserialize($result['description']),
                    'icon' => $result['icon'],
					'type_link' => $result['type_link'],
                    'link' => $result['link'],
                    'label_item' => $result['label_item'],
                    'icon_font' => $result['icon_font'],
					'class_menu' => $result['class_menu'],
                    'new_window' => $result['new_window'],
                    'status' => $result['status'],
                    'position' => $result['position'],
                    'submenu_width' => $result['submenu_width'],
                    'display_submenu' => $result['submenu_type'],
                    'content_width' => $result['content_width'],
                    'content_type' => $result['content_type'],
					'class_menu' => $result['class_menu'],
					'rang' => $result['rang'],
					'parent_id' => $result['parent_id'],
                    'content' => $check			
                );
				
            }
            return $data;
        }	
        return false;
    }

    public function getAllMenu($id) {
		
        $query = $this->db->query("SELECT * FROM ".DB_PREFIX."mega_menu WHERE module_id='".$id."'");      
		return $query->rows;
    }

    public function getCategories($array = array()) {
        $output = '';
        if(is_array($array) && !empty($array) && count($array)>0) {
            foreach($array as $row) {
                $output .= '<li class="dd-item" data-id="'.$row['id'].'" data-name="'.$row['name'].'">';
                $output .= '<a class="fa fa-times"></a>';
                $output .= '<div class="dd-handle">'.$row['name'].'</div>';
                if(isset($row['children'])) {
                    if(!empty($row['children'])) {
                        $output .= $this->getCategoriesChildren($row['children']);
                    }
                }
                $output .= '</li>';
            }
        }
        return $output;
    }

    public function getCategoriesChildren($array = array()) {
        $output = '';
        $output .= '<ol class="dd-list">';
        foreach($array as $row) {
            $output .= '<li class="dd-item" data-id="'.$row['id'].'" data-name="'.$row['name'].'">';
            $output .= '<a class="fa fa-times"></a>';
            $output .= '<div class="dd-handle">'.$row['name'].'</div>';
            if(isset($row['children'])) {
                if(!empty($row['children'])) {
                    $output .= $this->getCategoriesChildren($row['children']);
                }
            }
            $output .= '</li>';
        }
        $output .= '</ol>';
        return $output;
    }

    public function displayError() {
        $errors = '';
        foreach ($this->errors as $error) {
            $errors .= '<div>'.$error.'</div>';
        }
        return $errors;
    }

    public function install($module_id) {
        if($this->is_table_exist(DB_PREFIX . "mega_menu")) {
            $query = $this->db->query("
				CREATE TABLE IF NOT EXISTS `".DB_PREFIX."mega_menu` (
					`id` int(11) unsigned NOT NULL AUTO_INCREMENT,
					`id_old` int(11) NOT NULL,
					`parent_id` int(11) NOT NULL,
					`rang` int(11) NOT NULL,
					`icon` varchar(255) NOT NULL DEFAULT '',
					`name` text,
					`type_link` int(11),
					`module_id` int(11),
					`link` text,
					`description` text,
					`new_window` int(11) NOT NULL DEFAULT '0',
					`status` int(11) NOT NULL DEFAULT '0',
					`position` int(11) NOT NULL DEFAULT '0',
					`submenu_width` text,
					`submenu_type` int(11) NOT NULL DEFAULT '0',
					`content_width` int(11) NOT NULL DEFAULT '12',
					`content_type` int(11) NOT NULL DEFAULT '0',
					`content` text,
					`label_item` varchar(255) NOT NULL DEFAULT '',
					`icon_font` varchar(255) NOT NULL DEFAULT '',
					`class_menu` varchar(255),
					PRIMARY KEY (`id`)
				) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1
			");
            $query = $this->db->query("
                INSERT INTO `".DB_PREFIX."mega_menu` (`id`, `parent_id`, `rang`, `icon`, `name`, `type_link`, `module_id`, `link`, `description`, `new_window`, `status`, `position`, `submenu_width`, `submenu_type`, `content_width`, `content_type`, `content`, `label_item`, `icon_font`, `class_menu`) VALUES
                (90, 89, 1, 'http://localhost/ytc_extensions/opencart/image/cache/no_image-100x100.png', '{\"1\":\"Responsive theme\",\"3\":\"\",\"4\":\"\",\"2\":\"\"}', 0, ".$module_id.", '{\"url\":\"\",\"category\":\"\"}', '{\"1\":\"\",\"3\":\"\",\"4\":\"\",\"2\":\"\"}', 0, 0, 0, '100%', 0, 3, 0, NULL, 'hot', 'fa fa-camera-retro', NULL),
                (91, 89, 2, 'http://localhost/ytc_extensions/opencart/image/cache/no_image-100x100.png', '{\"1\":\"Categories hover\",\"3\":\"\",\"4\":\"\",\"2\":\"\"}', 0, ".$module_id.", '{\"url\":\"\",\"category\":\"\"}', '{\"1\":\"\",\"3\":\"\",\"4\":\"\",\"2\":\"\"}', 0, 0, 0, '100%', 0, 3, 2, '{\"html\":{\"text\":{\"1\":\"<p><br><\\/p>\",\"3\":\"<p><br><\\/p>\",\"4\":\"<p><br><\\/p>\",\"2\":\"<p><br><\\/p>\"}},\"product\":{\"id\":\"\",\"name\":\"\"},\"image\":{\"link\":\"no_image.png\",\"show_title\":\"1\"},\"subcategory\":{\"category\":\"\",\"limit_level_1\":\"\",\"limit_level_2\":\"\",\"show_title\":\"1\",\"show_image\":\"1\",\"columns\":\"1\",\"submenu\":\"1\",\"submenu_columns\":\"1\"},\"productlist\":{\"limit\":\"\",\"type\":\"new\",\"show_title\":\"1\",\"col\":\"\"},\"categories\":{\"categories\":[{\"name\":\"Components\",\"id\":25},{\"name\":\"Desktops > Mac\",\"id\":27},{\"name\":\"Laptops & Notebooks > Macs\",\"id\":46},{\"name\":\"MP3 Players\",\"id\":34},{\"name\":\"Components > Printers\",\"id\":30},{\"name\":\"Components > Scanners\",\"id\":31},{\"name\":\"Components > Web Cameras\",\"id\":32},{\"name\":\"Software\",\"id\":17},{\"name\":\"Cameras\",\"id\":33},{\"name\":\"Desktops > PC\",\"id\":26},{\"name\":\"Phones & PDAs\",\"id\":24},{\"name\":\"Laptops & Notebooks\",\"id\":18},{\"name\":\"Components > Monitors\",\"id\":28},{\"name\":\"Desktops\",\"id\":20},{\"name\":\"Components\\u00a0\\u00a0>\\u00a0\\u00a0Monitors\\u00a0\\u00a0>\\u00a0\\u00a0test 1\",\"id\":35},{\"name\":\"Software\",\"id\":17}],\"columns\":\"1\",\"submenu\":\"1\",\"submenu_columns\":\"1\"}}', 'hot', 'fa fa-camera-retro', NULL),
                (92, 89, 3, 'http://localhost/ytc_extensions/opencart/image/cache/no_image-100x100.png', '{\"1\":\"Categories visible\",\"3\":\"\",\"4\":\"\",\"2\":\"\"}', 0, ".$module_id.", '{\"url\":\"\",\"category\":\"\"}', '{\"1\":\"\",\"3\":\"\",\"4\":\"\",\"2\":\"\"}', 0, 0, 0, '100%', 0, 6, 2, '{\"html\":{\"text\":{\"1\":\"<p><br><\\/p>\",\"3\":\"<p><br><\\/p>\",\"4\":\"<p><br><\\/p>\",\"2\":\"<p><br><\\/p>\"}},\"Product\":{\"id\":\"\",\"name\":\"\"},\"image\":{\"link\":\"no_image.png\",\"show_title\":\"1\"},\"subcategory\":{\"category\":\"\",\"limit_level_1\":\"\",\"limit_level_2\":\"\",\"show_title\":\"1\",\"show_image\":\"1\",\"columns\":\"1\",\"submenu\":\"1\",\"submenu_columns\":\"1\"},\"productlist\":{\"limit\":\"\",\"type\":\"new\",\"show_title\":\"1\",\"col\":\"\"},\"categories\":{\"categories\":[{\"name\":\"Desktops > Mac\",\"id\":27,\"children\":[{\"name\":\"Laptops & Notebooks > Macs\",\"id\":46},{\"name\":\"Components > Printers\",\"id\":30},{\"name\":\"Phones & PDAs\",\"id\":24},{\"name\":\"Components > Monitors > test 2\",\"id\":36},{\"name\":\"MP3 Players > test 17\",\"id\":49}]},{\"name\":\"Components > Monitors > test 1\",\"id\":35,\"children\":[{\"name\":\"MP3 Players > test 11\",\"id\":43},{\"name\":\"MP3 Players > test 12\",\"id\":44},{\"name\":\"MP3 Players > test 20\",\"id\":52},{\"name\":\"Laptops & Notebooks\",\"id\":18},{\"name\":\"Components > Scanners\",\"id\":31}]},{\"name\":\"Software\",\"id\":17,\"children\":[{\"name\":\"Laptops & Notebooks > Macs\",\"id\":46},{\"name\":\"MP3 Players\",\"id\":34},{\"name\":\"Desktops > Mac\",\"id\":27},{\"name\":\"Components > Mice and Trackballs\",\"id\":29},{\"name\":\"Components > Monitors\",\"id\":28}]},{\"name\":\"Phones & PDAs\",\"id\":24,\"children\":[{\"name\":\"Components > Printers\",\"id\":30},{\"name\":\"Desktops > PC\",\"id\":26},{\"name\":\"MP3 Players > test 8\",\"id\":41},{\"name\":\"MP3 Players > test 7\",\"id\":40},{\"name\":\"MP3 Players > test 6\",\"id\":39}]}],\"columns\":\"2\",\"submenu\":\"2\",\"submenu_columns\":\"1\"}}', 'hot', 'fa fa-camera-retro', NULL),
                (94, 0, 4, 'http://localhost/ytc_extensions/opencart/image/cache/no_image-100x100.png', '{\"1\":\"Product\",\"3\":\"\",\"4\":\"\",\"2\":\"\"}', 0, ".$module_id.", '{\"url\":\"\",\"category\":\"\"}', '{\"1\":\"\",\"3\":\"\",\"4\":\"\",\"2\":\"\"}', 0, 0, 0, '100%', 0, 3, 1, '{\"html\":{\"text\":{\"1\":\"<p><br><\\/p>\",\"3\":\"<p><br><\\/p>\",\"4\":\"<p><br><\\/p>\",\"2\":\"<p><br><\\/p>\"}},\"Product\":{\"id\":\"42\",\"name\":\"Apple Cinema 30\"\"},\"image\":{\"link\":\"no_image.png\",\"show_title\":\"1\"},\"subcategory\":{\"category\":\"\",\"limit_level_1\":\"\",\"limit_level_2\":\"\",\"show_title\":\"1\",\"show_image\":\"1\",\"columns\":\"1\",\"submenu\":\"1\",\"submenu_columns\":\"1\"},\"productlist\":{\"limit\":\"\",\"type\":\"new\",\"show_title\":\"1\",\"col\":\"\"},\"categories\":{\"categories\":[],\"columns\":\"1\",\"submenu\":\"1\",\"submenu_columns\":\"1\"}}', 'hot', 'fa fa-car', NULL),
                (95, 94, 6, 'http://localhost/ytc_extensions/opencart/image/cache/no_image-100x100.png', '{\"1\":\"Product\",\"3\":\"\",\"4\":\"\",\"2\":\"\"}', 0, ".$module_id.", '{\"url\":\"\",\"category\":\"\"}', '{\"1\":\"\",\"3\":\"\",\"4\":\"\",\"2\":\"\"}', 0, 0, 0, '100%', 0, 3, 1, '{\"html\":{\"text\":{\"1\":\"&lt;p&gt;&lt;br&gt;&lt;\\/p&gt;\",\"3\":\"&lt;p&gt;&lt;br&gt;&lt;\\/p&gt;\",\"4\":\"&lt;p&gt;&lt;br&gt;&lt;\\/p&gt;\",\"2\":\"&lt;p&gt;&lt;br&gt;&lt;\\/p&gt;\"}},\"Product\":{\"id\":\"44\",\"name\":\"MacBook Air\"},\"image\":{\"link\":\"no_image.png\",\"show_title\":\"1\"},\"subcategory\":{\"category\":\"\",\"limit_level_1\":\"\",\"limit_level_2\":\"\",\"show_title\":\"1\",\"show_image\":\"1\",\"columns\":\"1\",\"submenu\":\"1\",\"submenu_columns\":\"1\"},\"productlist\":{\"limit\":\"\",\"type\":\"new\",\"show_title\":\"1\",\"col\":\"\"},\"categories\":{\"categories\":[],\"columns\":\"1\",\"submenu\":\"1\",\"submenu_columns\":\"1\"}}', 'hot', 'fa fa-camera-retro', NULL),
                (96, 94, 8, 'http://localhost/ytc_extensions/opencart/image/cache/no_image-100x100.png', '{\"1\":\"Product\",\"3\":\"\",\"4\":\"\",\"2\":\"\"}', 0, ".$module_id.", '{\"url\":\"\",\"category\":\"\"}', '{\"1\":\"\",\"3\":\"\",\"4\":\"\",\"2\":\"\"}', 0, 0, 0, '100%', 0, 3, 1, '{\"html\":{\"text\":{\"1\":\"&lt;p&gt;&lt;br&gt;&lt;\\/p&gt;\",\"3\":\"&lt;p&gt;&lt;br&gt;&lt;\\/p&gt;\",\"4\":\"&lt;p&gt;&lt;br&gt;&lt;\\/p&gt;\",\"2\":\"&lt;p&gt;&lt;br&gt;&lt;\\/p&gt;\"}},\"Product\":{\"id\":\"46\",\"name\":\"Sony VAIO\"},\"image\":{\"link\":\"no_image.png\",\"show_title\":\"1\"},\"subcategory\":{\"category\":\"\",\"limit_level_1\":\"\",\"limit_level_2\":\"\",\"show_title\":\"1\",\"show_image\":\"1\",\"columns\":\"1\",\"submenu\":\"1\",\"submenu_columns\":\"1\"},\"productlist\":{\"limit\":\"\",\"type\":\"new\",\"show_title\":\"1\",\"col\":\"\"},\"categories\":{\"categories\":[],\"columns\":\"1\",\"submenu\":\"1\",\"submenu_columns\":\"1\"}}', 'hot', 'fa fa-camera-retro', NULL),
                (97, 94, 5, 'http://localhost/ytc_extensions/opencart/image/cache/no_image-100x100.png', '{\"1\":\"Product\",\"3\":\"\",\"4\":\"\",\"2\":\"\"}', 0, ".$module_id.", '{\"url\":\"\",\"category\":\"\"}', '{\"1\":\"\",\"3\":\"\",\"4\":\"\",\"2\":\"\"}', 0, 0, 0, '100%', 0, 3, 1, '{\"html\":{\"text\":{\"1\":\"&lt;p&gt;&lt;br&gt;&lt;\\/p&gt;\",\"3\":\"&lt;p&gt;&lt;br&gt;&lt;\\/p&gt;\",\"4\":\"&lt;p&gt;&lt;br&gt;&lt;\\/p&gt;\",\"2\":\"&lt;p&gt;&lt;br&gt;&lt;\\/p&gt;\"}},\"Product\":{\"id\":\"29\",\"name\":\"Palm Treo Pro\"},\"image\":{\"link\":\"no_image.png\",\"show_title\":\"1\"},\"subcategory\":{\"category\":\"\",\"limit_level_1\":\"\",\"limit_level_2\":\"\",\"show_title\":\"1\",\"show_image\":\"1\",\"columns\":\"1\",\"submenu\":\"1\",\"submenu_columns\":\"1\"},\"productlist\":{\"limit\":\"\",\"type\":\"new\",\"show_title\":\"1\",\"col\":\"\"},\"categories\":{\"categories\":[],\"columns\":\"1\",\"submenu\":\"1\",\"submenu_columns\":\"1\"}}', 'hot', 'fa fa-camera-retro', NULL),
                (98, 94, 9, 'http://localhost/ytc_extensions/opencart/image/cache/no_image-100x100.png', '{\"1\":\"Manufacturer\",\"3\":\"\",\"4\":\"\",\"2\":\"\"}', 0, ".$module_id.", '{\"url\":\"\",\"category\":\"\"}', '{\"1\":\"\",\"3\":\"\",\"4\":\"\",\"2\":\"\"}', 0, 0, 0, '100%', 0, 3, 3, '{\"html\":{\"text\":{\"1\":\"&lt;p&gt;&lt;br&gt;&lt;\\/p&gt;\",\"3\":\"&lt;p&gt;&lt;br&gt;&lt;\\/p&gt;\",\"4\":\"&lt;p&gt;&lt;br&gt;&lt;\\/p&gt;\",\"2\":\"&lt;p&gt;&lt;br&gt;&lt;\\/p&gt;\"}},\"Product\":{\"id\":\"\",\"name\":\"\"},\"manufacture\":{\"name\":[\"Hewlett-Packard\",\"Palm\"],\"id\":[\"7\",\"6\"]},\"image\":{\"link\":\"no_image.png\",\"show_title\":\"1\"},\"subcategory\":{\"category\":\"\",\"limit_level_1\":\"\",\"limit_level_2\":\"\",\"show_title\":\"1\",\"show_image\":\"1\",\"columns\":\"1\",\"submenu\":\"1\",\"submenu_columns\":\"1\"},\"productlist\":{\"limit\":\"\",\"type\":\"new\",\"show_title\":\"1\",\"col\":\"\"},\"categories\":{\"categories\":[],\"columns\":\"1\",\"submenu\":\"1\",\"submenu_columns\":\"1\"}}', 'hot', 'fa fa-camera-retro', NULL),
                (100, 0, 12, 'http://localhost/ytc_extensions/opencart/image/cache/no_image-100x100.png', '{\"1\":\"Categories\",\"3\":\"\",\"4\":\"\",\"2\":\"\"}', 0, ".$module_id.", '{\"url\":\"\",\"category\":\"\"}', '{\"1\":\"\",\"3\":\"\",\"4\":\"\",\"2\":\"\"}', 0, 0, 0, '100%', 0, 4, 0, '{\"html\":{\"text\":{\"1\":\"&lt;p&gt;&lt;br&gt;&lt;\\/p&gt;\",\"3\":\"&lt;p&gt;&lt;br&gt;&lt;\\/p&gt;\",\"4\":\"&lt;p&gt;&lt;br&gt;&lt;\\/p&gt;\",\"2\":\"&lt;p&gt;&lt;br&gt;&lt;\\/p&gt;\"}},\"Product\":{\"id\":\"\",\"name\":\"\"},\"image\":{\"link\":\"no_image.png\",\"show_title\":\"1\"},\"subcategory\":{\"category\":\"\",\"limit_level_1\":\"\",\"limit_level_2\":\"\",\"show_title\":\"1\",\"show_image\":\"1\",\"columns\":\"1\",\"submenu\":\"1\",\"submenu_columns\":\"1\"},\"productlist\":{\"limit\":\"\",\"type\":\"new\",\"show_title\":\"1\",\"col\":\"\"},\"Categories\":{\"Categories\":[],\"columns\":\"1\",\"submenu\":\"1\",\"submenu_columns\":\"1\"}}', '', 'fa fa-taxi', NULL),
                (102, 0, 17, 'http://localhost/ytc_extensions/opencart/image/cache/no_image-100x100.png', '{\"1\":\"Blog\",\"3\":\"\",\"4\":\"\",\"2\":\"\"}', 0, ".$module_id.", '{\"url\":\"\",\"category\":\"\"}', '{\"1\":\"\",\"3\":\"\",\"4\":\"\",\"2\":\"\"}', 0, 0, 0, '100%', 0, 4, 0, '{\"html\":{\"text\":{\"1\":\"&lt;p&gt;&lt;br&gt;&lt;\\/p&gt;\",\"3\":\"&lt;p&gt;&lt;br&gt;&lt;\\/p&gt;\",\"4\":\"&lt;p&gt;&lt;br&gt;&lt;\\/p&gt;\",\"2\":\"&lt;p&gt;&lt;br&gt;&lt;\\/p&gt;\"}},\"Product\":{\"id\":\"\",\"name\":\"\"},\"image\":{\"link\":\"no_image.png\",\"show_title\":\"1\"},\"subcategory\":{\"category\":\"\",\"limit_level_1\":\"\",\"limit_level_2\":\"\",\"show_title\":\"1\",\"show_image\":\"1\",\"columns\":\"1\",\"submenu\":\"1\",\"submenu_columns\":\"1\"},\"productlist\":{\"limit\":\"\",\"type\":\"new\",\"show_title\":\"1\",\"col\":\"\"},\"categories\":{\"categories\":[],\"columns\":\"1\",\"submenu\":\"1\",\"submenu_columns\":\"1\"}}', '', 'fa fa-camera-retro', NULL),
                (103, 100, 16, 'http://localhost/ytc_extensions/opencart/image/cache/no_image-100x100.png', '{\"1\":\"Manufacturer\",\"3\":\"\",\"4\":\"\",\"2\":\"\"}', 0, ".$module_id.", '{\"url\":\"\",\"category\":\"\"}', '{\"1\":\"\",\"3\":\"\",\"4\":\"\",\"2\":\"\"}', 0, 0, 0, '100%', 0, 12, 3, '{\"html\":{\"text\":{\"1\":\"&lt;p&gt;&lt;br&gt;&lt;\\/p&gt;\",\"3\":\"&lt;p&gt;&lt;br&gt;&lt;\\/p&gt;\",\"4\":\"&lt;p&gt;&lt;br&gt;&lt;\\/p&gt;\",\"2\":\"&lt;p&gt;&lt;br&gt;&lt;\\/p&gt;\"}},\"Product\":{\"id\":\"\",\"name\":\"\"},\"manufacture\":{\"name\":[\"Apple\",\"Canon\",\"HTC\",\"Palm\",\"Hewlett-Packard\"],\"id\":[\"8\",\"9\",\"5\",\"6\",\"7\"]},\"image\":{\"link\":\"no_image.png\",\"show_title\":\"1\"},\"subcategory\":{\"category\":\"\",\"limit_level_1\":\"\",\"limit_level_2\":\"\",\"show_title\":\"1\",\"show_image\":\"1\",\"columns\":\"1\",\"submenu\":\"1\",\"submenu_columns\":\"1\"},\"productlist\":{\"limit\":\"\",\"type\":\"new\",\"show_title\":\"1\",\"col\":\"\"},\"categories\":{\"categories\":[],\"columns\":\"1\",\"submenu\":\"1\",\"submenu_columns\":\"1\"}}', 'hot', 'fa fa-camera-retro', NULL),
                (115, 114, 25, 'http://localhost/ytc_extensions/opencart/image/cache/no_image-100x100.png', '{\"1\":\"Macbook\",\"3\":\"\",\"4\":\"\",\"2\":\"\"}', 0, ".$module_id.", '{\"url\":\"\",\"category\":\"\"}', '{\"1\":\"\",\"3\":\"\",\"4\":\"\",\"2\":\"\"}', 0, 0, 0, '100%', 0, 3, 4, '{\"html\":{\"text\":{\"1\":\"&lt;p&gt;&lt;br&gt;&lt;\\/p&gt;\",\"3\":\"&lt;p&gt;&lt;br&gt;&lt;\\/p&gt;\",\"4\":\"&lt;p&gt;&lt;br&gt;&lt;\\/p&gt;\",\"2\":\"&lt;p&gt;&lt;br&gt;&lt;\\/p&gt;\"}},\"Product\":{\"id\":\"\",\"name\":\"\"},\"image\":{\"link\":\"catalog\\/demo\\/hp_1.jpg\",\"show_title\":\"1\"},\"subcategory\":{\"category\":\"\",\"limit_level_1\":\"\",\"limit_level_2\":\"\",\"show_title\":\"1\",\"show_image\":\"1\",\"columns\":\"1\",\"submenu\":\"1\",\"submenu_columns\":\"1\"},\"productlist\":{\"limit\":\"\",\"type\":\"new\",\"show_title\":\"1\",\"col\":\"\"},\"categories\":{\"categories\":[],\"columns\":\"1\",\"submenu\":\"1\",\"submenu_columns\":\"1\"}}', '', '', NULL),
                (84, 0, 18, 'http://localhost/ytc_extensions/opencart/image/cache/no_image-100x100.png', '{\"1\":\"Buy this theme\",\"3\":\"\",\"4\":\"\",\"2\":\"\"}', 0, ".$module_id.", '{\"url\":\"\",\"category\":\"\"}', '{\"1\":\"\",\"3\":\"\",\"4\":\"\",\"2\":\"\"}', 0, 0, 1, '80%', 1, 4, 0, '{\"html\":{\"text\":{\"1\":\"adfdf\",\"3\":\"\\u00e1dfasdf\",\"4\":\"\\u00e1dfadsf\",\"2\":\"adfdasfadsf\"}},\"Product\":{\"id\":\"\",\"name\":\"\"},\"image\":{\"link\":\"no_image.png\",\"show_title\":\"1\"},\"subcategory\":{\"category\":\"\",\"limit_level_1\":\"\",\"limit_level_2\":\"\",\"show_title\":\"1\",\"show_image\":\"1\",\"columns\":\"1\",\"submenu\":\"1\",\"submenu_columns\":\"1\"},\"productlist\":{\"limit\":\"\",\"type\":\"new\",\"show_title\":\"1\",\"col\":\"\"},\"categories\":{\"categories\":[],\"columns\":\"1\",\"submenu\":\"1\",\"submenu_columns\":\"1\"}}', '', 'fa fa-camera-retro', NULL),
                (85, 84, 19, 'http://localhost/ytc_extensions/opencart/image/cache/no_image-100x100.png', '{\"1\":\"Item 1\",\"3\":\"\",\"4\":\"\",\"2\":\"\"}', 0, ".$module_id.", '{\"url\":\"\",\"category\":\"\"}', '{\"1\":\"\",\"3\":\"\",\"4\":\"\",\"2\":\"\"}', 0, 0, 0, '100%', 0, 3, 0, '{\"html\":{\"text\":{\"1\":\"<p>ÁGDFKHGFKDGH</p>\",\"3\":\"&lt;p&gt;&lt;br&gt;&lt;\\/p&gt;\",\"4\":\"&lt;p&gt;&lt;br&gt;&lt;\\/p&gt;\",\"2\":\"&lt;p&gt;&lt;br&gt;&lt;\\/p&gt;\"}},\"Product\":{\"id\":\"\",\"name\":\"\"},\"image\":{\"link\":\"no_image.png\",\"show_title\":\"1\"},\"subcategory\":{\"category\":\"\",\"limit_level_1\":\"\",\"limit_level_2\":\"\",\"show_title\":\"1\",\"show_image\":\"1\",\"columns\":\"1\",\"submenu\":\"1\",\"submenu_columns\":\"1\"},\"productlist\":{\"limit\":\"\",\"type\":\"new\",\"show_title\":\"1\",\"col\":\"\"},\"categories\":{\"categories\":[],\"columns\":\"1\",\"submenu\":\"1\",\"submenu_columns\":\"1\"}}', 'hot', 'fa fa-camera-retro', NULL),
                (86, 84, 20, 'http://localhost/ytc_extensions/opencart/image/cache/no_image-100x100.png', '{\"1\":\"Item 2\",\"3\":\"\",\"4\":\"\",\"2\":\"\"}', 0, ".$module_id.", '{\"url\":\"\",\"category\":\"\"}', '{\"1\":\"\",\"3\":\"\",\"4\":\"\",\"2\":\"\"}', 0, 0, 0, '100%', 0, 3, 0, NULL, 'hot', 'fa fa-camera-retro', NULL),
                (87, 84, 21, 'http://localhost/ytc_extensions/opencart/image/cache/no_image-100x100.png', '{\"1\":\"Item 3\",\"3\":\"\",\"4\":\"\",\"2\":\"\"}', 0, ".$module_id.", '{\"url\":\"\",\"category\":\"\"}', '{\"1\":\"\",\"3\":\"\",\"4\":\"\",\"2\":\"\"}', 0, 0, 0, '100%', 0, 3, 0, NULL, 'hot', 'fa fa-camera-retro', NULL),
                (88, 84, 22, 'http://localhost/ytc_extensions/opencart/image/cache/no_image-100x100.png', '{\"1\":\"Item 4\",\"3\":\"\",\"4\":\"\",\"2\":\"\"}', 0, ".$module_id.", '{\"url\":\"\",\"category\":\"\"}', '{\"1\":\"\",\"3\":\"\",\"4\":\"\",\"2\":\"\"}', 0, 0, 0, '100%', 0, 3, 0, NULL, 'hot', 'fa fa-camera-retro', NULL),
                (99, 0, 10, 'http://localhost/ytc_extensions/opencart/image/cache/no_image-100x100.png', '{\"1\":\"Women\",\"3\":\"\",\"4\":\"\",\"2\":\"\"}', 0, ".$module_id.", '{\"url\":\"\",\"category\":\"\"}', '{\"1\":\"\",\"3\":\"\",\"4\":\"\",\"2\":\"\"}', 0, 0, 0, '25%', 0, 4, 0, '{\"html\":{\"text\":{\"1\":\"&lt;p&gt;&lt;br&gt;&lt;\\/p&gt;\",\"3\":\"&lt;p&gt;&lt;br&gt;&lt;\\/p&gt;\",\"4\":\"&lt;p&gt;&lt;br&gt;&lt;\\/p&gt;\",\"2\":\"&lt;p&gt;&lt;br&gt;&lt;\\/p&gt;\"}},\"Product\":{\"id\":\"\",\"name\":\"\"},\"image\":{\"link\":\"no_image.png\",\"show_title\":\"1\"},\"subcategory\":{\"category\":\"\",\"limit_level_1\":\"\",\"limit_level_2\":\"\",\"show_title\":\"1\",\"show_image\":\"1\",\"columns\":\"1\",\"submenu\":\"1\",\"submenu_columns\":\"1\"},\"productlist\":{\"limit\":\"\",\"type\":\"new\",\"show_title\":\"1\",\"col\":\"\"},\"categories\":{\"categories\":[],\"columns\":\"1\",\"submenu\":\"1\",\"submenu_columns\":\"1\"}}', '', 'fa fa-rocket', NULL),
                (89, 0, 0, 'http://localhost/ytc_extensions/opencart/image/cache/no_image-100x100.png', '{\"1\":\"Men\",\"3\":\"\",\"4\":\"\",\"2\":\"\"}', 0, ".$module_id.", '{\"url\":\"\",\"category\":\"\"}', '{\"1\":\"\",\"3\":\"\",\"4\":\"\",\"2\":\"\"}', 0, 0, 0, '100%', 0, 4, 0, '{\"html\":{\"text\":{\"1\":\"<p><br><\\/p>\",\"3\":\"<p><br><\\/p>\",\"4\":\"<p><br><\\/p>\",\"2\":\"<p><br><\\/p>\"}},\"Product\":{\"id\":\"\",\"name\":\"\"},\"image\":{\"link\":\"no_image.png\",\"show_title\":\"1\"},\"subcategory\":{\"category\":\"\",\"limit_level_1\":\"\",\"limit_level_2\":\"\",\"show_title\":\"1\",\"show_image\":\"1\",\"columns\":\"1\",\"submenu\":\"1\",\"submenu_columns\":\"1\"},\"productlist\":{\"limit\":\"\",\"type\":\"new\",\"show_title\":\"1\",\"col\":\"\"},\"categories\":{\"categories\":[],\"columns\":\"1\",\"submenu\":\"1\",\"submenu_columns\":\"1\"}}', '', 'fa fa-camera-retro', NULL),
                (104, 100, 15, 'http://localhost/ytc_extensions/opencart/image/cache/no_image-100x100.png', '{\"1\":\"Product\",\"3\":\"\",\"4\":\"\",\"2\":\"\"}', 0, ".$module_id.", '{\"url\":\"\",\"category\":\"\"}', '{\"1\":\"\",\"3\":\"\",\"4\":\"\",\"2\":\"\"}', 0, 0, 0, '100%', 0, 3, 1, '{\"html\":{\"text\":{\"1\":\"&lt;p&gt;&lt;br&gt;&lt;\\/p&gt;\",\"3\":\"&lt;p&gt;&lt;br&gt;&lt;\\/p&gt;\",\"4\":\"&lt;p&gt;&lt;br&gt;&lt;\\/p&gt;\",\"2\":\"&lt;p&gt;&lt;br&gt;&lt;\\/p&gt;\"}},\"Product\":{\"id\":\"45\",\"name\":\"MacBook Pro\"},\"image\":{\"link\":\"no_image.png\",\"show_title\":\"1\"},\"subcategory\":{\"category\":\"\",\"limit_level_1\":\"\",\"limit_level_2\":\"\",\"show_title\":\"1\",\"show_image\":\"1\",\"columns\":\"1\",\"submenu\":\"1\",\"submenu_columns\":\"1\"},\"productlist\":{\"limit\":\"\",\"type\":\"new\",\"show_title\":\"1\",\"col\":\"\"},\"categories\":{\"categories\":[],\"columns\":\"1\",\"submenu\":\"1\",\"submenu_columns\":\"1\"}}', 'hot', 'fa fa-camera-retro', NULL),
                (105, 100, 14, 'http://localhost/ytc_extensions/opencart/image/cache/no_image-100x100.png', '{\"1\":\"Categories visible\",\"3\":\"\",\"4\":\"\",\"2\":\"\"}', 0, ".$module_id.", '{\"url\":\"\",\"category\":\"\"}', '{\"1\":\"\",\"3\":\"\",\"4\":\"\",\"2\":\"\"}', 0, 0, 0, '100%', 0, 3, 2, '{\"html\":{\"text\":{\"1\":\"&lt;p&gt;&lt;br&gt;&lt;\\/p&gt;\",\"3\":\"&lt;p&gt;&lt;br&gt;&lt;\\/p&gt;\",\"4\":\"&lt;p&gt;&lt;br&gt;&lt;\\/p&gt;\",\"2\":\"&lt;p&gt;&lt;br&gt;&lt;\\/p&gt;\"}},\"Product\":{\"id\":\"\",\"name\":\"\"},\"image\":{\"link\":\"no_image.png\",\"show_title\":\"1\"},\"subcategory\":{\"category\":\"\",\"limit_level_1\":\"\",\"limit_level_2\":\"\",\"show_title\":\"1\",\"show_image\":\"1\",\"columns\":\"1\",\"submenu\":\"1\",\"submenu_columns\":\"1\"},\"productlist\":{\"limit\":\"\",\"type\":\"new\",\"show_title\":\"1\",\"col\":\"\"},\"categories\":{\"categories\":[{\"name\":\"Phones & PDAs\",\"id\":24,\"children\":[{\"name\":\"Desktops > PC\",\"id\":26},{\"name\":\"Phones & PDAs\",\"id\":24},{\"name\":\"MP3 Players\",\"id\":34},{\"name\":\"Laptops & Notebooks > Macs\",\"id\":46},{\"name\":\"Desktops > Mac\",\"id\":27},{\"name\":\"Phones & PDAs\",\"id\":24}]},{\"name\":\"Components > Mice and Trackballs\",\"id\":29,\"children\":[{\"name\":\"Components > Monitors\",\"id\":28},{\"name\":\"Components > Monitors > test 1\",\"id\":35},{\"name\":\"Software\",\"id\":17},{\"name\":\"Components > Scanners\",\"id\":31},{\"name\":\"Components\",\"id\":25}]}],\"columns\":\"1\",\"submenu\":\"2\",\"submenu_columns\":\"1\"}}', 'hot', 'fa fa-camera-retro', NULL),
                (106, 100, 13, 'http://localhost/ytc_extensions/opencart/image/cache/no_image-100x100.png', '{\"1\":\"Categories visible\",\"3\":\"\",\"4\":\"\",\"2\":\"\"}', 0, ".$module_id.", '{\"url\":\"\",\"category\":\"\"}', '{\"1\":\"\",\"3\":\"\",\"4\":\"\",\"2\":\"\"}', 0, 0, 0, '100%', 0, 6, 2, '{\"html\":{\"text\":{\"1\":\"&lt;p&gt;&lt;br&gt;&lt;\\/p&gt;\",\"3\":\"&lt;p&gt;&lt;br&gt;&lt;\\/p&gt;\",\"4\":\"&lt;p&gt;&lt;br&gt;&lt;\\/p&gt;\",\"2\":\"&lt;p&gt;&lt;br&gt;&lt;\\/p&gt;\"}},\"Product\":{\"id\":\"\",\"name\":\"\"},\"image\":{\"link\":\"no_image.png\",\"show_title\":\"1\"},\"subcategory\":{\"category\":\"\",\"limit_level_1\":\"\",\"limit_level_2\":\"\",\"show_title\":\"1\",\"show_image\":\"1\",\"columns\":\"1\",\"submenu\":\"1\",\"submenu_columns\":\"1\"},\"productlist\":{\"limit\":\"\",\"type\":\"new\",\"show_title\":\"1\",\"col\":\"\"},\"categories\":{\"categories\":[{\"name\":\"Components > Mice and Trackballs\",\"id\":29,\"children\":[{\"name\":\"Components > Monitors\",\"id\":28},{\"name\":\"Desktops > Mac\",\"id\":27},{\"name\":\"Laptops & Notebooks > Macs\",\"id\":46},{\"name\":\"MP3 Players\",\"id\":34},{\"name\":\"Components > Monitors > test 1\",\"id\":35},{\"name\":\"Components > Monitors > test 2\",\"id\":36},{\"name\":\"MP3 Players > test 8\",\"id\":41},{\"name\":\"MP3 Players > test 6\",\"id\":39},{\"name\":\"MP3 Players > test 5\",\"id\":37},{\"name\":\"MP3 Players > test 4\",\"id\":38},{\"name\":\"MP3 Players > test 24\",\"id\":56},{\"name\":\"MP3 Players > test 23\",\"id\":55},{\"name\":\"MP3 Players > test 21\",\"id\":53},{\"name\":\"Components\",\"id\":25},{\"name\":\"Cameras\",\"id\":33}]},{\"name\":\"Components > Printers\",\"id\":30,\"children\":[{\"name\":\"Phones & PDAs\",\"id\":24},{\"name\":\"Desktops > PC\",\"id\":26},{\"name\":\"Software\",\"id\":17},{\"name\":\"Components > Scanners\",\"id\":31},{\"name\":\"Desktops\",\"id\":20},{\"name\":\"Laptops & Notebooks\",\"id\":18},{\"name\":\"Laptops & Notebooks > Macs\",\"id\":46},{\"name\":\"MP3 Players\",\"id\":34},{\"name\":\"Components > Mice and Trackballs\",\"id\":29},{\"name\":\"Components > Monitors\",\"id\":28},{\"name\":\"Desktops > Mac\",\"id\":27},{\"name\":\"Software\",\"id\":17},{\"name\":\"Components > Scanners\",\"id\":31},{\"name\":\"Components\",\"id\":25},{\"name\":\"Cameras\",\"id\":33}]}],\"columns\":\"1\",\"submenu\":\"2\",\"submenu_columns\":\"3\"}}', 'hot', 'fa fa-camera-retro', NULL),
                (107, 99, 11, 'http://localhost/ytc_extensions/opencart/image/cache/no_image-100x100.png', '{\"1\":\"categories1\",\"3\":\"\",\"4\":\"\",\"2\":\"\"}', 0, ".$module_id.", '{\"url\":\"\",\"category\":\"\"}', '{\"1\":\"\",\"3\":\"\",\"4\":\"\",\"2\":\"\"}', 0, 0, 0, '50%', 0, 12, 2, '{\"html\":{\"text\":{\"1\":\"&lt;p&gt;&lt;br&gt;&lt;\\/p&gt;\",\"3\":\"&lt;p&gt;&lt;br&gt;&lt;\\/p&gt;\",\"4\":\"&lt;p&gt;&lt;br&gt;&lt;\\/p&gt;\",\"2\":\"&lt;p&gt;&lt;br&gt;&lt;\\/p&gt;\"}},\"Product\":{\"id\":\"\",\"name\":\"\"},\"image\":{\"link\":\"no_image.png\",\"show_title\":\"1\"},\"subcategory\":{\"category\":\"\",\"limit_level_1\":\"\",\"limit_level_2\":\"\",\"show_title\":\"1\",\"show_image\":\"1\",\"columns\":\"1\",\"submenu\":\"1\",\"submenu_columns\":\"1\"},\"productlist\":{\"limit\":\"\",\"type\":\"new\",\"show_title\":\"1\",\"col\":\"\"},\"categories\":{\"categories\":[{\"name\":\"Components\",\"id\":25,\"children\":[{\"name\":\"Components\\u00a0\\u00a0>\\u00a0\\u00a0Monitors\",\"id\":28,\"children\":[{\"name\":\"Components\",\"id\":25}]},{\"name\":\"Components\\u00a0\\u00a0>\\u00a0\\u00a0Mice and Trackballs\",\"id\":29,\"children\":[{\"name\":\"Components\\u00a0\\u00a0>\\u00a0\\u00a0Monitors\",\"id\":28}]}]}],\"columns\":\"1\",\"submenu\":\"1\",\"submenu_columns\":\"1\"}}', '', '', NULL),
                (116, 114, 26, 'http://localhost/ytc_extensions/opencart/image/cache/no_image-100x100.png', '{\"1\":\"Television\",\"3\":\"\",\"4\":\"\",\"2\":\"\"}', 0, ".$module_id.", '{\"url\":\"\",\"category\":\"\"}', '{\"1\":\"\",\"3\":\"\",\"4\":\"\",\"2\":\"\"}', 0, 0, 0, '100%', 0, 3, 4, '{\"html\":{\"text\":{\"1\":\"&lt;p&gt;&lt;br&gt;&lt;\\/p&gt;\",\"3\":\"&lt;p&gt;&lt;br&gt;&lt;\\/p&gt;\",\"4\":\"&lt;p&gt;&lt;br&gt;&lt;\\/p&gt;\",\"2\":\"&lt;p&gt;&lt;br&gt;&lt;\\/p&gt;\"}},\"Product\":{\"id\":\"\",\"name\":\"\"},\"image\":{\"link\":\"catalog\\/demo\\/hp_2.jpg\",\"show_title\":\"1\"},\"subcategory\":{\"category\":\"\",\"limit_level_1\":\"\",\"limit_level_2\":\"\",\"show_title\":\"1\",\"show_image\":\"1\",\"columns\":\"1\",\"submenu\":\"1\",\"submenu_columns\":\"1\"},\"productlist\":{\"limit\":\"\",\"type\":\"new\",\"show_title\":\"1\",\"col\":\"\"},\"categories\":{\"categories\":[],\"columns\":\"1\",\"submenu\":\"1\",\"submenu_columns\":\"1\"}}', '', '', NULL),
                (117, 114, 27, 'http://localhost/ytc_extensions/opencart/image/cache/no_image-100x100.png', '{\"1\":\"Ipad\",\"3\":\"\",\"4\":\"\",\"2\":\"\"}', 0, ".$module_id.", '{\"url\":\"\",\"category\":\"\"}', '{\"1\":\"\",\"3\":\"\",\"4\":\"\",\"2\":\"\"}', 0, 0, 0, '100%', 0, 3, 4, '{\"html\":{\"text\":{\"1\":\"&lt;p&gt;&lt;br&gt;&lt;\\/p&gt;\",\"3\":\"&lt;p&gt;&lt;br&gt;&lt;\\/p&gt;\",\"4\":\"&lt;p&gt;&lt;br&gt;&lt;\\/p&gt;\",\"2\":\"&lt;p&gt;&lt;br&gt;&lt;\\/p&gt;\"}},\"Product\":{\"id\":\"\",\"name\":\"\"},\"image\":{\"link\":\"catalog\\/demo\\/hp_3.jpg\",\"show_title\":\"1\"},\"subcategory\":{\"category\":\"\",\"limit_level_1\":\"\",\"limit_level_2\":\"\",\"show_title\":\"1\",\"show_image\":\"1\",\"columns\":\"1\",\"submenu\":\"1\",\"submenu_columns\":\"1\"},\"productlist\":{\"limit\":\"\",\"type\":\"new\",\"show_title\":\"1\",\"col\":\"\"},\"categories\":{\"categories\":[],\"columns\":\"1\",\"submenu\":\"1\",\"submenu_columns\":\"1\"}}', '', '', NULL),
                (114, 0, 23, 'http://localhost/ytc_extensions/opencart/image/cache/no_image-100x100.png', '{\"1\":\"Image\",\"3\":\"\",\"4\":\"\",\"2\":\"\"}', 0, ".$module_id.", '{\"url\":\"\",\"category\":\"\"}', '{\"1\":\"\",\"3\":\"\",\"4\":\"\",\"2\":\"\"}', 0, 0, 0, '100%', 0, 4, 0, '{\"html\":{\"text\":{\"1\":\"&lt;p&gt;&lt;br&gt;&lt;\\/p&gt;\",\"3\":\"&lt;p&gt;&lt;br&gt;&lt;\\/p&gt;\",\"4\":\"&lt;p&gt;&lt;br&gt;&lt;\\/p&gt;\",\"2\":\"&lt;p&gt;&lt;br&gt;&lt;\\/p&gt;\"}},\"Product\":{\"id\":\"\",\"name\":\"\"},\"image\":{\"link\":\"no_image.png\",\"show_title\":\"1\"},\"subcategory\":{\"category\":\"\",\"limit_level_1\":\"\",\"limit_level_2\":\"\",\"show_title\":\"1\",\"show_image\":\"1\",\"columns\":\"1\",\"submenu\":\"1\",\"submenu_columns\":\"1\"},\"productlist\":{\"limit\":\"\",\"type\":\"new\",\"show_title\":\"1\",\"col\":\"\"},\"categories\":{\"categories\":[],\"columns\":\"1\",\"submenu\":\"1\",\"submenu_columns\":\"1\"}}', '', 'fa fa-picture-o', NULL),
                (111, 94, 7, 'http://localhost/ytc_extensions/opencart/image/cache/no_image-100x100.png', '{\"1\":\"Product\",\"3\":\"\",\"4\":\"\",\"2\":\"\"}', 0, ".$module_id.", '{\"url\":\"\",\"category\":\"\"}', '{\"1\":\"\",\"3\":\"\",\"4\":\"\",\"2\":\"\"}', 0, 0, 0, '100%', 0, 3, 1, '{\"html\":{\"text\":{\"1\":\"&lt;p&gt;&lt;br&gt;&lt;\\/p&gt;\",\"3\":\"&lt;p&gt;&lt;br&gt;&lt;\\/p&gt;\",\"4\":\"&lt;p&gt;&lt;br&gt;&lt;\\/p&gt;\",\"2\":\"&lt;p&gt;&lt;br&gt;&lt;\\/p&gt;\"}},\"Product\":{\"id\":\"46\",\"name\":\"Sony VAIO\"},\"image\":{\"link\":\"no_image.png\",\"show_title\":\"1\"},\"subcategory\":{\"category\":\"\",\"limit_level_1\":\"\",\"limit_level_2\":\"\",\"show_title\":\"1\",\"show_image\":\"1\",\"columns\":\"1\",\"submenu\":\"1\",\"submenu_columns\":\"1\"},\"productlist\":{\"limit\":\"\",\"type\":\"new\",\"show_title\":\"1\",\"col\":\"\"},\"categories\":{\"categories\":[],\"columns\":\"1\",\"submenu\":\"1\",\"submenu_columns\":\"1\"}}', '', '', NULL),
                (119, 114, 24, 'http://localhost/ytc_extensions/opencart/image/cache/no_image-100x100.png', '{\"1\":\"Laptop\",\"3\":\"\",\"4\":\"\",\"2\":\"\"}', 0, ".$module_id.", '{\"url\":\"\",\"category\":\"\"}', '{\"1\":\"\",\"3\":\"\",\"4\":\"\",\"2\":\"\"}', 0, 0, 0, '100%', 2, 3, 4, '{\"html\":{\"text\":{\"1\":\"&lt;p&gt;&lt;br&gt;&lt;\\/p&gt;\",\"3\":\"&lt;p&gt;&lt;br&gt;&lt;\\/p&gt;\",\"4\":\"&lt;p&gt;&lt;br&gt;&lt;\\/p&gt;\",\"2\":\"&lt;p&gt;&lt;br&gt;&lt;\\/p&gt;\"}},\"Product\":{\"id\":\"\",\"name\":\"\"},\"image\":{\"link\":\"catalog\\/demo\\/hp_2.jpg\",\"show_title\":\"1\"},\"subcategory\":{\"category\":\"\",\"limit_level_1\":\"\",\"limit_level_2\":\"\",\"show_title\":\"1\",\"show_image\":\"1\",\"columns\":\"1\",\"submenu\":\"1\",\"submenu_columns\":\"1\"},\"productlist\":{\"limit\":\"\",\"type\":\"new\",\"show_title\":\"1\",\"col\":\"\"},\"categories\":{\"categories\":[],\"columns\":\"1\",\"submenu\":\"1\",\"submenu_columns\":\"1\"}}', '', '', NULL),
                (120, 121, 29, 'http://localhost/ytc_extensions/opencart/image/cache/no_image-100x100.png', '{\"1\":\"Product List\",\"3\":\"\",\"4\":\"\",\"2\":\"\"}', 0, ".$module_id.", '{\"url\":\"\",\"category\":\"\"}', '{\"1\":\"\",\"3\":\"\",\"4\":\"\",\"2\":\"\"}', 0, 0, 0, '100%', 0, 12, 6, '{\"html\":{\"text\":{\"1\":\"&lt;p&gt;&lt;br&gt;&lt;\\/p&gt;\",\"3\":\"&lt;p&gt;&lt;br&gt;&lt;\\/p&gt;\",\"4\":\"&lt;p&gt;&lt;br&gt;&lt;\\/p&gt;\",\"2\":\"&lt;p&gt;&lt;br&gt;&lt;\\/p&gt;\"}},\"Product\":{\"id\":\"\",\"name\":\"\"},\"image\":{\"link\":\"no_image.png\",\"show_title\":\"1\"},\"subcategory\":{\"category\":\"\",\"limit_level_1\":\"\",\"limit_level_2\":\"\",\"show_title\":\"1\",\"show_image\":\"1\",\"columns\":\"1\",\"submenu\":\"1\",\"submenu_columns\":\"1\"},\"productlist\":{\"limit\":\"4\",\"type\":\"popular\",\"show_title\":\"1\",\"col\":\"4\"},\"categories\":{\"categories\":[],\"columns\":\"1\",\"submenu\":\"1\",\"submenu_columns\":\"1\"}}', '', '', NULL),
                (121, 0, 28, 'http://localhost/ytc_extensions/opencart/image/cache/no_image-100x100.png', '{\"1\":\"Product List\",\"3\":\"\",\"4\":\"\",\"2\":\"\"}', 0, ".$module_id.", '{\"url\":\"\",\"category\":\"\"}', '{\"1\":\"\",\"3\":\"\",\"4\":\"\",\"2\":\"\"}', 0, 0, 0, '100%', 0, 4, 0, '{\"html\":{\"text\":{\"1\":\"&lt;p&gt;&lt;br&gt;&lt;\\/p&gt;\",\"3\":\"&lt;p&gt;&lt;br&gt;&lt;\\/p&gt;\",\"4\":\"&lt;p&gt;&lt;br&gt;&lt;\\/p&gt;\",\"2\":\"&lt;p&gt;&lt;br&gt;&lt;\\/p&gt;\"}},\"Product\":{\"id\":\"\",\"name\":\"\"},\"image\":{\"link\":\"no_image.png\",\"show_title\":\"1\"},\"subcategory\":{\"category\":\"\",\"limit_level_1\":\"\",\"limit_level_2\":\"\",\"show_title\":\"1\",\"show_image\":\"1\",\"columns\":\"1\",\"submenu\":\"1\",\"submenu_columns\":\"1\"},\"productlist\":{\"limit\":\"\",\"type\":\"new\",\"show_title\":\"1\",\"col\":\"\"},\"categories\":{\"categories\":[],\"columns\":\"1\",\"submenu\":\"1\",\"submenu_columns\":\"1\"}}', '', '', NULL)
            ");
        }
        return false;
    }

    public function uninstall() {
        $this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "mega_menu`");
        //$this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "settting  WHERE code = 'so_megamenu'");
    }

    public function skrut($c,$d) {
        if(strlen($c) > $d) {
            $ciag = substr($c,0,$d);
            $ciag .="...";
            return $ciag;
        } else {
            return $c;
        }
    }

    public function is_table_exist($table){
        $query = $this->db->query("SHOW TABLES LIKE '".$table."'");
        if( count($query->rows) <= 0 ) {
            return true;
        }
        return false;
    }
	public function getModuleId() {
		$sql = " SHOW TABLE STATUS LIKE '" . DB_PREFIX . "module'" ;
		$query = $this->db->query($sql);
		return $query->rows;
	}
	public function duplicateModule($module_id,$import_module){
		$parent_menu = $this->getMenuByIdModule($import_module);
		if($parent_menu){
			foreach ($parent_menu as $menu) {
				$dane = $this->model_extension_module_so_megamenu->getMenu(intval($menu['id']));
				$dane['module_id'] = $module_id;
				$id_parent_add = $this->model_extension_module_so_megamenu->addMenu($dane);
				$subcategories = $this->model_extension_module_so_megamenu->getSubMenu(intval($menu['id']));
				if($subcategories){
				foreach ($subcategories as $result) {
					$data = array(
							'parent_id' => $id_parent_add,
							'name' => unserialize($result['name']),
							'description' => unserialize($result['description']),
							'icon' => $result['icon'],
							'module_id' => $module_id,
							'link' => $result['link'],
							'type_link' => $result['type_link'],
							'new_window' => $result['new_window'],
							'status' => $result['status'],
							'position' => $result['position'],
							'submenu_width' => $result['submenu_width'],
							'display_submenu' => $result['submenu_type'],
							'content_width' => $result['content_width'],
							'content_type' => $result['content_type'],
							'content' => unserialize($result['content']),
							'list_categories' => (isset($result['content']['categories']['categories']) && $result['content']['categories']['categories']) ? $this->model_extension_module_so_megamenu->getCategories(unserialize($result['content']['categories']['categories'])) : ''
						);
						$this->model_extension_module_so_megamenu->addMenu($data);
					}
				}
			}
		}
	}
	public function getMenuByIdModule($module_id){
		$query = $this->db->query("SELECT * FROM ".DB_PREFIX."mega_menu WHERE  module_id = '".$module_id."' AND parent_id=0");
		return $query->rows;
	}
	public function duplicateMenu($id_duplicate){
		$dane = $this->model_extension_module_so_megamenu->getMenu(intval($id_duplicate));
		$id_parent_add = $this->model_extension_module_so_megamenu->addMenu($dane);
		$subcategories = $this->model_extension_module_so_megamenu->getSubMenu(intval($id_duplicate));
		
		if($subcategories){
			foreach ($subcategories as $result) {
				$data = array(
					'parent_id' => $id_parent_add,
					'name' => unserialize($result['name']),
					'description' => unserialize($result['description']),
					'icon' => $result['icon'],
					'link' => $result['link'],
					'type_link' => $result['type_link'],
					'new_window' => $result['new_window'],
					'status' => $result['status'],
					'position' => $result['position'],
					'submenu_width' => $result['submenu_width'],
					'display_submenu' => $result['submenu_type'],
					'content_width' => $result['content_width'],
					'content_type' => $result['content_type'],
					'content' => unserialize($result['content']),
					'list_categories' => (isset($result['content']['categories']['categories']) && $result['content']['categories']['categories']) ? $this->model_extension_module_so_megamenu->getCategories(unserialize($result['content']['categories']['categories'])) : ''
				);
				$this->model_extension_module_so_megamenu->addMenu($data);
			}
		}
		
		return $id_parent_add;
	}
}
?>
