<?php
/**
 * @version 1.5 beta 5 $Id: templates.php 183 2009-11-18 10:30:48Z vistamedia $
 * @package Joomla
 * @subpackage FLEXIcontent
 * @copyright (C) 2009 Emmanuel Danan - www.vistamedia.fr
 * @license GNU/GPL v2
 * 
 * FLEXIcontent is a derivative work of the excellent QuickFAQ component
 * @copyright (C) 2008 Christoph Lukes
 * see www.schlu.net for more information
 *
 * FLEXIcontent is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 */

defined( '_JEXEC' ) or die( 'Restricted access' );

jimport('joomla.application.component.controller');

/**
 * FLEXIcontent Component Templates Controller
 *
 * @package Joomla
 * @subpackage FLEXIcontent
 * @since 1.0
 */
class FlexicontentControllerTemplates extends FlexicontentController
{
	/**
	 * Constructor
	 *
	 * @since 1.0
	 */
	function __construct()
	{
		parent::__construct();

		// Register Extra task
		$this->registerTask( 'add'  ,		'edit' );
		$this->registerTask( 'apply', 		'save' );
		$this->registerTask( 'import', 		'import' );
		$this->registerTask( 'duplicate', 	'duplicate' );
		$this->registerTask( 'remove', 		'remove' );
	}
		
	function duplicate()
	{
		// Check for request forgeries
		JRequest::checkToken() or jexit( 'Invalid Token' );

		$source 		= JRequest::getCmd('source');
		$dest 			= JRequest::getCmd('dest');
		
		$model = $this->getModel('templates');
		
		if (!$model->duplicate($source, $dest)) {
			echo JText::sprintf( 'FLEXI_TEMPLATE_FAILED_CLONE', $source );
			return;
		} else {
			echo '<span class="copyok" style="margin-top:15px; display:block">'.JText::sprintf( 'FLEXI_TEMPLATE_CLONED', $source, $dest ).'</span>';
		}
	}
	
	function remove()
	{
		// Check for request forgeries
		JRequest::checkToken( 'request' ) or jexit( 'Invalid Token' );
		$dir = JRequest::getCmd('dir');

		$model = $this->getModel('templates');
		
		if (!$model->delete($dir)) {
			echo '<td colspan="5" align="center">';
			echo JText::sprintf( 'FLEXI_TEMPLATE_FAILED_DELETE', $dir );
			echo '</td>';
			return;
		} else {
			echo '<td colspan="5" align="center">';
			echo '<span class="copyok">'.JText::sprintf( 'FLEXI_TEMPLATE_DELETED', $dir ).'</span>';
			echo '</td>';
		}
	}

	/**
	 * Logic to save a tag
	 *
	 * @access public
	 * @return void
	 * @since 1.0
	 */
	function save()
	{
		// Check for request forgeries
		JRequest::checkToken() or jexit( 'Invalid Token' );
		
		$task		= JRequest::getVar('task');
		$type 		= JRequest::getVar('type',  'items', '', 'word');
		$folder 	= JRequest::getVar('folder',  'default', '', 'cmd');
		$positions 	= JRequest::getVar('positions',  '');
		
		$positions = explode(',', $positions);
		
		//Sanitize
		$post = JRequest::get( 'post' );
		$model = $this->getModel('template');

		foreach ($positions as $p) {
			$model->store($folder, $type, $p, $post[$p]);
		}

		switch ($task)
		{
			case 'apply' :
				$link = 'index.php?option=com_flexicontent&view=template&type='.$type.'&folder='.$folder;
				break;

			default :
				$link = 'index.php?option=com_flexicontent&view=templates';
				break;
		}
		$msg = JText::_( 'FLEXI_SAVE_FIELD_POSITIONS' );

		$this->setRedirect($link, $msg);
	}

	/**
	 * Logic to publish categories
	 *
	 * @access public
	 * @return void
	 * @since 1.0
	 */
	function publish()
	{
		$cid 	= JRequest::getVar( 'cid', array(0), 'post', 'array' );

		if (!is_array( $cid ) || count( $cid ) < 1) {
			$msg = '';
			JError::raiseWarning(500, JText::_( 'FLEXI_SELECT_ITEM_PUBLISH' ) );
		} else {
			$model = $this->getModel('Templates');

			if(!$model->publish($cid, 1)) {
				JError::raiseError( 500, $model->getError() );
			}

			$total = count( $cid );
			$msg 	= $total.' '.JText::_( 'FLEXI_TAG_PUBLISHED' );
		}
		
		$this->setRedirect( 'index.php?option=com_flexicontent&view=templates', $msg );
	}

	/**
	 * Logic to unpublish categories
	 *
	 * @access public
	 * @return void
	 * @since 1.0
	 */
	function unpublish()
	{
		$cid 	= JRequest::getVar( 'cid', array(0), 'post', 'array' );

		if (!is_array( $cid ) || count( $cid ) < 1) {
			$msg = '';
			JError::raiseWarning(500, JText::_( 'FLEXI_SELECT_ITEM_UNPUBLISH' ) );
		} else {
			$model = $this->getModel('templates');

			if(!$model->publish($cid, 0)) {
				JError::raiseError( 500, $model->getError() );
			}

			$total = count( $cid );
			$msg 	= $total.' '.JText::_( 'FLEXI_TAG_UNPUBLISHED' );
			$cache = &JFactory::getCache('com_flexicontent');
			$cache->clean();
		}
		
		$this->setRedirect( 'index.php?option=com_flexicontent&view=templates', $msg );
	}

	/**
	 * logic for cancel an action
	 *
	 * @access public
	 * @return void
	 * @since 1.0
	 */
	function cancel()
	{
		// Check for request forgeries
		JRequest::checkToken() or jexit( 'Invalid Token' );

		$this->setRedirect( 'index.php?option=com_flexicontent&view=templates' );
	}

	/**
	 * Logic to create the view for the edit categoryscreen
	 *
	 * @access public
	 * @return void
	 * @since 1.0
	 */
	function edit( )
	{
		JRequest::setVar( 'view', 'tag' );
		JRequest::setVar( 'hidemainmenu', 1 );

		$model 	= $this->getModel('tag');
		$user	=& JFactory::getUser();

		// Error if checkedout by another administrator
		if ($model->isCheckedOut( $user->get('id') )) {
			$this->setRedirect( 'index.php?option=com_flexicontent&view=tags', JText::_( 'FLEXI_EDITED_BY_ANOTHER_ADMIN' ) );
		}

		$model->checkout( $user->get('id') );

		parent::display();
	}

	/**
	 *  Add new Tag from item screen
	 *
	 */
	function addtag(){
		$name 	= JRequest::getString('name', '');
		$model 	= $this->getModel('tag');
		$model->addtag($name);
	}

	/**
	 * Logic to import a tag list
	 *
	 * @access public
	 * @return void
	 * @since 1.5
	 */
	function import( )
	{
		// Check for request forgeries
		JRequest::checkToken() or jexit( 'Invalid Token' );
		
		$list		= JRequest::getVar( 'taglist', null, 'post', 'string' );

		$model	= $this->getModel('tags');		
		$logs 	= $model->importList($list);
		
		if ($logs) {
			if ($logs['success']) {
				echo '<div class="copyok">'.JText::sprintf( 'FLEXI_TAG_IMPORT_SUCCESS', $logs['success'] ).'</div>';
			}
			if ($logs['error']) {
				echo '<div class="copywarn>'.JText::sprintf( 'FLEXI_TAG_IMPORT_FAILED', $logs['error'] ).'</div>';
			}
		} else {
			echo '<div class="copyfailed">'.JText::_( 'FLEXI_NO_TAG_TO_IMPORT' ).'</div>';
		}
	}
}