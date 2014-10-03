<?php
/**
 * HTML5 Template
 * @version 1.5 stable $Id: category_items_html5.php 0001 2012-09-23 14:00:28Z Rehne $
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
// first define the template name
$tmpl = $this->tmpl;
$user = JFactory::getUser();

JFactory::getDocument()->addScript( JURI::base(true).'/components/com_flexicontent/assets/js/tabber-minimized.js');
JFactory::getDocument()->addStyleSheet(JURI::base(true).'/components/com_flexicontent/assets/css/tabber.css');
?>

<?php
	ob_start();
	
	// Form for (a) Text search, Field Filters, Alpha-Index, Items Total Statistics, Selectors(e.g. per page, orderby)
	// If customizing via CSS rules or JS scripts is not enough, then please copy the following file here to customize the HTML too
	include(JPATH_SITE.DS.'components'.DS.'com_flexicontent'.DS.'tmpl_common'.DS.'listings_filter_form_html5.php');
	
	$filter_form_html = trim(ob_get_contents());
	ob_end_clean();
	if ( $filter_form_html ) {
		echo '<aside class="group">'."\n".$filter_form_html."\n".'</aside>';
	}
?>

<div class="fcclear"></div>

<?php
if (!$this->items) {
	// No items exist
	if ($this->getModel()->getState('limit')) {
		// Not creating a category view without items
		echo '<div class="noitems group">' . JText::_( 'FLEXI_NO_ITEMS_FOUND' ) . '</div>';
	}
	return;
}

$items = & $this->items;


// ***********
// DECIDE TAGS 
// ***********
// Main container
$mainAreaTag = 'div';

// SEO, header level of title tag
$itemTitleHeaderLevel = '2';
	
// SEO, header level of tab title tag
$tabsHeaderLevel = $itemTitleHeaderLevel == '2'  ?  '3' : '2';  	
?>

<!-- tabber start -->
<div class="fctabber group">
<?php
foreach ($items as $i => $item) :
	
	$fc_item_classes = 'tabbertab';
	
	$markup_tags = '<span class="fc_mublock">';
	foreach($item->css_markups as $grp => $css_markups) {
		if ( empty($css_markups) )  continue;
		$fc_item_classes .= ' fc'.implode(' fc', $css_markups);
		
		$ecss_markups  = $item->ecss_markups[$grp];
		$title_markups = $item->title_markups[$grp];
		foreach($css_markups as $mui => $css_markup) {
			$markup_tags .= '<span class="fc_markup mu' . $css_markups[$mui] . $ecss_markups[$mui] .'">' .$title_markups[$mui]. '</span>';
		}
	}
	$markup_tags .= '</span>';
?>

<!-- tab start -->
<?php echo '<'.$mainAreaTag; ?> id="tablist_item_<?php echo $i; ?>" class="<?php echo $fc_item_classes; ?> group">
	<h3 class="tabberheading"><?php echo mb_substr ($item->title, 0, 20, 'utf-8'); ?></h3><!-- tab title -->
	
	<?php echo ( ($mainAreaTag == 'section') ? '<header>' : ''); ?>
	
  <?php if ($item->event->beforeDisplayContent) : ?>
	  <!-- BOF beforeDisplayContent -->
		<aside class="fc_beforeDisplayContent group">
			<?php echo $item->event->beforeDisplayContent; ?>
		</aside>
		<!-- EOF beforeDisplayContent -->
	<?php endif; ?>
	
	<?php
	$show_editbutton = $this->params->get('show_editbutton', 1);
	$pdfbutton = flexicontent_html::pdfbutton( $item, $this->params );
	$mailbutton = flexicontent_html::mailbutton( FLEXI_ITEMVIEW, $this->params, $item->categoryslug, $item->slug, 0, $item );
	$printbutton = flexicontent_html::printbutton( $this->print_link, $this->params );
	$editbutton = $show_editbutton ? flexicontent_html::editbutton( $item, $this->params ) : '';
	$statebutton = $show_editbutton ? flexicontent_html::statebutton( $item, $this->params ) : '';
	$approvalbutton = flexicontent_html::approvalbutton( $item, $this->params );
	?>
	
	<?php if ($pdfbutton || $mailbutton || $printbutton || $editbutton || $statebutton || $approvalbutton) : ?>
		<!-- BOF buttons -->
		<p class="buttons">
			<?php echo $pdfbutton; ?>
			<?php echo $mailbutton; ?>
			<?php echo $printbutton; ?>
			<?php echo $editbutton; ?>
			<?php echo $statebutton; ?>
			<?php echo $approvalbutton; ?>
		</p>
		<!-- EOF buttons -->
	<?php endif; ?>
	
	
	<?php echo ( ($mainAreaTag == 'section') ? '</header>' : ''); ?>
	
	<?php echo ( ($mainAreaTag == 'section') ? '<article>' : ''); ?>
	
	<?php
		$header_shown =
			$this->params->get('show_comments_count', 1) ||
			$this->params->get('show_title', 1) || $item->event->afterDisplayTitle ||
			isset($item->positions['subtitle1']) || isset($item->positions['subtitle2']) || isset($item->positions['subtitle3']);
	?>
	
	<?php if ( $header_shown ) : ?>
	<header class="group">
	<?php endif; ?>
	
	<?php if ($this->params->get('show_comments_count')) : ?>
		<?php if ( isset($this->comments[ $item->id ]->total) ) : ?>
			<div class="fc_comments_count hasTip" alt="<?php echo JText::_('FLEXI_NUM_OF_COMMENTS');?>" title="<?php echo JText::_('FLEXI_NUM_OF_COMMENTS');?>::<?php echo JText::_('FLEXI_NUM_OF_COMMENTS_TIP');?>">
				<?php echo $this->comments[ $item->id ]->total; ?>
			</div>
		<?php endif; ?>
	<?php endif; ?>
	
	
	<?php if ($this->params->get('show_title', 1)) : ?>
		<!-- BOF item title -->
		<?php echo '<h'.$itemTitleHeaderLevel; ?> class="contentheading"><span class="fc_item_title">
			<?php
				echo ( mb_strlen($item->title, 'utf-8') > $this->params->get('title_cut_text',200) ) ?
					mb_substr ($item->title, 0, $this->params->get('title_cut_text',200), 'utf-8') . ' ...'  :  $item->title;
			?>
		</span><?php echo '</h'.$itemTitleHeaderLevel; ?>>
		<!-- EOF item title -->
	<?php endif; ?>
	
	<?php echo $markup_tags; ?>
	
  <?php if ($item->event->afterDisplayTitle) : ?>
	  <!-- BOF afterDisplayTitle -->
		<div class="fc_afterDisplayTitle group">
			<?php echo $item->event->afterDisplayTitle; ?>
		</div>
	  <!-- EOF afterDisplayTitle -->
	<?php endif; ?>
	
	
	<?php if (isset($item->positions['subtitle1'])) : ?>
		<!-- BOF subtitle1 block -->
		<div class="flexi lineinfo subtitle1 group">
			<?php foreach ($item->positions['subtitle1'] as $field) : ?>
			<div class="flexi element">
				<?php if ($field->label) : ?>
				<span class="flexi label field_<?php echo $field->name; ?>"><?php echo $field->label; ?></span>
				<?php endif; ?>
				<div class="flexi value field_<?php echo $field->name; ?>"><?php echo $field->display; ?></div>
			</div>
			<?php endforeach; ?>
		</div>
		<!-- EOF subtitle1 block -->
	<?php endif; ?>
	
	
	<?php if (isset($item->positions['subtitle2'])) : ?>
		<!-- BOF subtitle2 block -->
		<div class="flexi lineinfo subtitle2 group">
			<?php foreach ($item->positions['subtitle2'] as $field) : ?>
			<div class="flexi element">
				<?php if ($field->label) : ?>
				<span class="flexi label field_<?php echo $field->name; ?>"><?php echo $field->label; ?></span>
				<?php endif; ?>
				<div class="flexi value field_<?php echo $field->name; ?>"><?php echo $field->display; ?></div>
			</div>
			<?php endforeach; ?>
		</div>
		<!-- EOF subtitle2 block -->
	<?php endif; ?>
	
	
	<?php if (isset($item->positions['subtitle3'])) : ?>
		<!-- BOF subtitle3 block -->
		<div class="flexi lineinfo subtitle3 group">
			<?php foreach ($item->positions['subtitle3'] as $field) : ?>
			<div class="flexi element">
				<?php if ($field->label) : ?>
				<span class="flexi label field_<?php echo $field->name; ?>"><?php echo $field->label; ?></span>
				<?php endif; ?>
				<div class="flexi value field_<?php echo $field->name; ?>"><?php echo $field->display; ?></div>
			</div>
			<?php endforeach; ?>
		</div>
		<!-- EOF subtitle3 block -->
	<?php endif; ?>
	
	<?php if ( $header_shown ) : ?>
	</header>
	<?php endif; ?>
	
	
	<div class="fcclear"></div>
	
	<?php if ((isset($item->positions['image'])) || (isset($item->positions['top']))) : ?>
		<!-- BOF image/top row -->
		<aside class="flexi topblock group row">  <!-- NOTE: image block is inside top block ... -->
			
			<?php if (isset($item->positions['image'])) : ?>
				<!-- BOF image block -->
				<?php foreach ($item->positions['image'] as $field) : ?>
				<figure class="flexi image field_<?php echo $field->name; ?> span4">
					<?php echo $field->display; ?>
					<div class="fcclear"></div>
				</figure>
				<?php endforeach; ?>
				<!-- EOF image block -->
			<?php endif; ?>
			
			<?php if (isset($item->positions['top'])) : ?>
				<!-- BOF top block -->
				<?php
					$top_cols = $this->params->get('top_cols', 'two');
					$span_class = $top_cols == 'one' ? 'span12' : 'span6'; // bootstrap span
				?>
				<div class="flexi infoblock <?php echo $top_cols; ?>cols group span8">
					<ul class="flexi row">
						<?php foreach ($item->positions['top'] as $field) : ?>
						<li class="flexi <?php echo $span_class; ?>">
							<div>
								<?php if ($field->label) : ?>
								<span class="flexi label field_<?php echo $field->name; ?>"><?php echo $field->label; ?></span>
								<?php endif; ?>
								<div class="flexi value field_<?php echo $field->name; ?>"><?php echo $field->display; ?></div>
							</div>
						</li>
						<?php endforeach; ?>
					</ul>
				</div>
				<!-- EOF top block -->
			<?php endif; ?>
			
		</aside>
		<!-- EOF image/top row -->
	<?php endif; ?>
	
	
	<div class="fcclear"></div>
	
	
	<?php if (isset($item->toc)) : ?>
		<!-- BOF TOC -->
		<?php echo $item->toc; ?>
		<!-- EOF TOC -->
	<?php endif; ?>
	
	
	<?php if (isset($item->positions['description'])) : ?>
		<!-- BOF description -->
		<div class="description group">
			<?php foreach ($item->positions['description'] as $field) : ?>
				<?php if ($field->label) : ?>
			<div class="desc-title"><?php echo $field->label; ?></div>
				<?php endif; ?>
			<div class="desc-content"><?php echo $field->display; ?></div>
			<?php endforeach; ?>
		</div>
		<!-- EOF description -->
	<?php endif; ?>
	
	
	<div class="fcclear"></div>
	
	
	<?php
		$readmore_shown  = $this->params->get('show_readmore', 1) && strlen(trim($item->fulltext)) >= 1;
		$footer_shown = $readmore_shown ||
			isset($item->positions['bottom']) || $item->event->afterDisplayContent;
	?>
	
	<?php if ( $footer_shown ) : ?>
	<footer class="group">
	<?php endif; ?>
	
	<?php if (isset($item->positions['bottom'])) : ?>
		<!-- BOF bottom block -->
		<?php
			$bottom_cols = $this->params->get('bottom_cols', 'two');
			$span_class = $bottom_cols == 'one' ? 'span12' : 'span6'; // bootstrap span
		?>
		<div class="flexi infoblock <?php echo $bottom_cols; ?>cols group">
			<ul class="flexi row">
				<?php foreach ($item->positions['bottom'] as $field) : ?>
				<li class="flexi <?php echo $span_class; ?>">
					<div>
						<?php if ($field->label) : ?>
						<span class="flexi label field_<?php echo $field->name; ?>"><?php echo $field->label; ?></span>
						<?php endif; ?>
						<div class="flexi value field_<?php echo $field->name; ?>"><?php echo $field->display; ?></div>
					</div>
				</li>
				<?php endforeach; ?>
			</ul>
		</div>
		<!-- EOF bottom block -->
	<?php endif; ?>
	
	
	<?php if ( $readmore_shown ) : ?>
	<span class="readmore group">
		<a href="<?php echo JRoute::_(FlexicontentHelperRoute::getItemRoute($item->slug, $item->categoryslug, 0, $item)); ?>" class="readon">
			<?php echo ' ' . ($item->params->get('readmore')  ?  $item->params->get('readmore') : JText::sprintf('FLEXI_READ_MORE', $item->title)); ?>
		</a>
	</span>
	<?php endif; ?>
	
	
	<?php if ($item->event->afterDisplayContent) : ?>
		<!-- BOF afterDisplayContent -->
		<aside class="fc_afterDisplayContent group">
			<?php echo $item->event->afterDisplayContent; ?>
		</aside>
		<!-- EOF afterDisplayContent -->
	<?php endif; ?>
	
	<?php if ( $footer_shown) : ?>
	</footer>
	<?php endif; ?>
	
	<?php echo $mainAreaTag == 'section' ? '</article>' : ''; ?>
	
	<?php if ($this->params->get('show_comments_incat') && !JRequest::getVar('print')) : /* PARAMETER MISSING */?>
		<!-- BOF comments -->
		<section class="comments group">
		<?php
			if ($this->params->get('comments') == 1) :
				if (file_exists(JPATH_SITE.DS.'components'.DS.'com_jcomments'.DS.'jcomments.php')) :
					require_once(JPATH_SITE.DS.'components'.DS.'com_jcomments'.DS.'jcomments.php');
					echo JComments::showComments($item->id, 'com_flexicontent', $this->escape($item->title));
				endif;
			endif;
	
			if ($this->params->get('comments') == 2) :
				if (file_exists(JPATH_SITE.DS.'plugins'.DS.'content'.DS.'jom_comment_bot.php')) :
		  			require_once(JPATH_SITE.DS.'plugins'.DS.'content'.DS.'jom_comment_bot.php');
		  			echo jomcomment($item->id, 'com_flexicontent');
					endif;
				endif;
		?>
		</section>
		<!-- BOF comments -->
	<?php endif; ?>

<?php echo '</'.$mainAreaTag.'>'; ?>
<!-- tab end -->


<?php endforeach; /* item loop */ ?>
		
</div>
<!-- tabber end -->
