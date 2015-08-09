<?php
defined('_JEXEC') or die;

$doc = JFactory::getDocument();
$doc->addStylesheet(JURI::root(true) . '/media/mod_zachallenge/'.'/css/styles.css');
?>

<div class="zc-<?php echo $moduleclass_sfx ?>">
	<h4>
	<a target="_blank" href="<?php echo $data->uri;?>">
	<img class="zc-logo-xs" src="<?php echo $data->logo_xs;?>"/>
	<?php echo $data->name; ?>
	</a>
	</h4>
	<p><?php echo $data->desc; ?></p>
	<table class="table table-condensed zc-borderless">
		<thead>
			<tr>
				<th><?php echo JText::_('MOD_ZACHALLENGE_DISTANCE');?></th>	
				<th><?php echo JText::_('MOD_ZACHALLENGE_PARTICIPANTS');?></th>
				<th><?php echo JText::_('MOD_ZACHALLENGE_GOAL');?></th>
				<th><?php echo JText::_('MOD_ZACHALLENGE_PRIZE');?></th>
				<th><?php echo JText::_('MOD_ZACHALLENGE_START');?></th>
				<th><?php echo JText::_('MOD_ZACHALLENGE_END');?></th>
			</tr>
		</thead>
		<tbody>
			<tr class="success">
				<td><strong><?php echo $data->odo; ?></strong></td>
				<td><?php echo $data->attn_count; ?></td>
				<td><?php echo $data->goal; ?></td>
				<td><?php echo $data->prize; ?></td>
				<td><?php echo $data->start; ?></td>
				<td><?php echo $data->end; ?></td>
			</tr>
		</tbody>
	</table>
	<table class="table table-condensed table-hover">
		<thead>
			<tr>
				<th><?php echo JText::_('MOD_ZACHALLENGE_POS');?></th>
				<th><?php echo JText::_('MOD_ZACHALLENGE_USERNAME');?></th>
				<th><?php echo JText::_('MOD_ZACHALLENGE_DISTANCE');?></th>
			</tr>
		</thead>
		<tbody>
			<?php for ($i = 1; $i < 11; $i++) { ?>
			<tr>
				<th scope="row" width="5"><?php echo $i; ?></th>
				<td>
				<a target="_blank" href="<?php echo $data->ranking[$i]['profile'];?>">
				<?php if ($params->get('show_avatars') == 1) : ?>
				<img class="zc-avatar" src="<?php echo $data->ranking[$i]['avatar']?>" alt=""/>
				<?php endif; ?>
				<?php echo $data->ranking[$i]['name']; ?>
				</a>
				</td>
				<td>
				<?php echo $data->ranking[$i]['distance'];?>
				</td>
			</tr>
			<?php } ?>
		</tbody>
	</table>
</div>
