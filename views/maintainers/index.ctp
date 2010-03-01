<?php $this->Html->h2(__('Maintainers', true));?>
<table cellpadding="0" cellspacing="0">
<tr>
		<th><?php echo $this->Paginator->sort('username');?></th>
		<th><?php echo $this->Paginator->sort('url');?></th>
		<th class="actions"><?php __('Actions');?></th>
</tr>
<?php
$i = 0;
foreach ($maintainers as $maintainer):
	$class = null;
	if ($i++ % 2 == 0) {
		$class = ' class="altrow"';
	}
?>
<tr<?php echo $class;?>>
	<td>
		<?php echo $this->Html->link($maintainer['Maintainer']['username'], array('action' => 'view', $maintainer['Maintainer']['username'])); ?>&nbsp;
		<?php echo ($maintainer['Maintainer']['name'] != ' ' and $maintainer['Maintainer']['name'] != '') ? "({$maintainer['Maintainer']['name']})" : ''; ?>
	</td>
	<td><?php echo (!empty($maintainer['Maintainer']['url'])) ? $this->Html->link($maintainer['Maintainer']['url'], $maintainer['Maintainer']['url']) : ''; ?>&nbsp;</td>
	<td class="actions">
		<?php echo $this->Html->link(__('Edit', true), array('action' => 'edit', $maintainer['Maintainer']['id'])); ?>
		<?php echo $this->Html->link(__('Delete', true), array('action' => 'delete', $maintainer['Maintainer']['id']), null, sprintf(__('Are you sure you want to delete # %s?', true), $maintainer['Maintainer']['id'])); ?>
	</td>
</tr>
<?php endforeach; ?>
</table>
<p>
<?php
echo $this->Paginator->counter(array(
'format' => __('Page %page% of %pages%, showing %current% records out of %count% total, starting on record %start%, ending on %end%', true)
));
?>	</p>

<div class="paging">
	<?php echo $this->Paginator->prev('<< '.__('previous', true), array(), null, array('class'=>'disabled'));?>
 | 	<?php echo $this->Paginator->numbers();?>
|
	<?php echo $this->Paginator->next(__('next', true).' >>', array(), null, array('class' => 'disabled'));?>
</div>