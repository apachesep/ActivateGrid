<?php
// No direct access to this file
defined('_JEXEC') or die('Restricted access');
define('DS','/');
/**
 * Script file of ActivateGrid component
 */
class com_activategridInstallerScript
{
        /**
         * method to install the component
         *
         * @return void
         */
        function install($parent) 
        {
            self::generateSocialColorPalette();
            self::createImageDirectory("instagram");
            self::createImageDirectory("facebook");
            self::createImageDirectory("storify");
            $parent->getParent()->setRedirectURL('index.php?option=com_activategrid');
        }
 
        /**
         * method to uninstall the component
         *
         * @return void
         */
        function uninstall($parent) 
        {          
            echo '<p>' . JText::_('COM_ACTIVATEGRID_UNINSTALL_TEXT') . '</p>';
        }
 
        /**
         * method to update the component
         *
         * @return void
         */
        function update($parent) 
        {
            self::generateSocialColorPalette();
            self::createImageDirectory("instagram");
            self::createImageDirectory("facebook");
            self::createImageDirectory("storify");
            echo '<div class="alert alert-info">';
            echo '  <p>' . JText::_('COM_ACTIVATEGRID_UPDATE_TEXT').' to the version: '.$parent->get('manifest')->version. '</p>';
            echo '</div>';
        }
 
        /**
         * method to run before an install/update/uninstall method
         *
         * @return void
         */
        function preflight($type, $parent) 
        {
            echo '<div class="alert alert-success">';
            echo '  <p>' . JText::_('COM_ACTIVATEGRID_INSTALL_TEXT' ) . '</p>';
            echo '</div>';
            
            echo '<p><a href="index.php?option=com_config&view=component&component=com_activategrid"><button class="btn btn-large btn-block btn-success" type="button">'.JText::_('COM_ACTIVATEGRID_GET_START' ).'</button></a></p>';
        }
 
        /**
         * method to run after an install/update/uninstall method
         *
         * @return void
         */
        function postflight($type, $parent) 
        {
            /* I fix lft and rgt attribute in the #__categories for the created categories */
            self::fixCategoryAttributes();
        }
        
        function generateSocialColorPalette()
        {
            $socialnetowks_default_color = array("twitter"   => "#201f1f",
                                                 "instagram" => "#5e4439",
                                                 "facebook" => "#3B5998",
                                                 "storify" => "#DFE5EA");
            
            $db = JFactory::getDBO();

            foreach($socialnetowks_default_color as $socialnetwork => $bgcolor)
            {
                $category_id = self::checkCategoryExists($socialnetwork);
                if($category_id)
                {
                    if(!self::checkExistingCategoryColorPalette("cti", $category_id)) 
                        $db->setQuery("INSERT INTO #__activategrid (context, name, value) VALUES ('category_color', 'cti".$category_id."', '#3584bc');")->query();
                    if(!self::checkExistingCategoryColorPalette("cte", $category_id)) 
                        $db->setQuery("INSERT INTO #__activategrid (context, name, value) VALUES ('category_color', 'cte".$category_id."', '#ffffff');")->query();
                    if(!self::checkExistingCategoryColorPalette("cbg", $category_id)) 
                        $db->setQuery("INSERT INTO #__activategrid (context, name, value) VALUES ('category_color', 'cbg".$category_id."', '".$bgcolor."');")->query();
                }
            }
        }
        
        function checkExistingCategoryColorPalette($name = 'cbg', $category_id = 0)
        {             
             $db = JFactory::getDBO();
             $db->setQuery("SELECT id FROM #__activategrid WHERE name='".$name.$category_id."'")->query();
             $result = $db->loadResult();
             if($result) return $result;
             else return false;             
        }
         
        function checkCategoryExists($alias = "")
        {
            $db = JFactory::getDBO();
            $alias = htmlspecialchars(addslashes($alias));
            $query = "SELECT id FROM #__categories WHERE alias = '".$alias."' AND published=1;";
            $db->setQuery($query);
            $db->query();
            $results = $db->loadObjectList();   
            if(sizeof($results) > 0) return $results[0]->id; // exists
            else return false; // not exists
        }
        
        function createImageDirectory($name)
        {
            $images_directory = JPATH_ROOT.DS."images".DS.$name.DS;
            self::makeDir($images_directory);
        }
        
        private static function makeDir($path)
        {
           return is_dir($path) || mkdir($path);
        }
        
        public function DLog($str)
        {
            if(is_array($str))
                echo "<pre>".print_r($str,true)."</pre>";
            else if(is_object($str))
                echo "<pre>".print_r($str,true)."</pre>";
            else
                echo "<pre>".$str."</pre>";
        }
        
                /* this fix lft and rgt attribute in the #__categories for the created categories */
        public static function fixCategoryAttributes()
        {
            $db = JFactory::getDBO();
            $query = "SELECT MAX(rgt) FROM #__categories";
            $db->setQuery($query);
            $rgt = $db->loadResult();

            $query = "SELECT id FROM #__categories WHERE lft = 0 AND rgt = 0 AND parent_id = 1";
            $db->setQuery($query);
            $elements = $db->loadObjectList();

            foreach($elements as $categoryToFix)
            {                    
                $tmpLFT = $rgt+1;
                $tmpRGT = $tmpLFT+1;
                $query = "UPDATE #__categories SET lft = $tmpLFT, rgt = $tmpRGT WHERE id = ".$categoryToFix->id;
                $db->setQuery($query);
                $db->execute();
                $rgt++;
            }            
        }
}