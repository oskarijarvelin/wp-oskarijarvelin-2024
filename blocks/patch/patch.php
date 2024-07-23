<?php if ( have_rows('fixtures', get_the_ID()) ) : ?>
	<table class="fixtures">
		<?php $fixtures = get_field('fixtures', get_the_ID()); ?>
		<?php while ( have_rows('fixtures', get_the_ID()) ) : the_row(); ?>
			<?php $fix_id = get_sub_field('fixture'); ?>

			<?php
				$total_pcs = 0;
				$universes = [];
				if ( have_rows('groups') ) :
					while ( have_rows('groups') ) : the_row();
						$total_pcs += get_sub_field('amount');
						$universes[] = get_sub_field('uni');
					endwhile;
				endif;
			?>


			<tr>
				<?php $parent_manufacturer = wp_get_post_terms( $fix_id, 'manufacturer', array( 'fields' => 'ids' ) ); ?>
				<td title="Manufacturer" colspan="2"><b><?php echo get_term( $parent_manufacturer[0] )->name; ?></b></td>
				<td title="Fixture Name" colspan="2"><?php echo get_field( 'fixture', $fix_id ); ?></td>
				<td title="Fixture Mode"><b>MODE:</b> <?php echo get_field( 'mode', $fix_id ) ?></td>
				<td><b>Total <?php echo $total_pcs; ?> piece<?php if ( $total_pcs > 1 ) { echo 's'; } ?> in <?php echo count($universes); ?> universe<?php if ( count($universes) > 1 ) { echo 's'; } ?></b></td>
			</tr>

			<tr>
				<td title="Pieces"><b>Pcs</b></td>
				<td title="Universe"><b>Uni</b></td>
				<td title="ID"><b>ID</b></td>
				<td title="Position"><b>Position</b></td>
				<td title="Addresses" colspan="2"><b>Addresses</b></td>
			</tr>

			<?php if ( have_rows('groups') ) : ?>
				<?php while ( have_rows('groups') ) : the_row(); ?>
					<?php $sa = get_sub_field('starting_address'); ?>
					<?php $amount = get_sub_field('amount'); ?>
					<?php $chs = get_field( 'channels', $fix_id ); ?>
					<tr>
						<td title="Pieces"><?php echo $amount; ?></td>
						<td title="Universe"><?php the_sub_field('uni'); ?></td>
						<td title="ID"><?php the_sub_field('starting_id'); ?><?php if ( $amount > 1 ) { echo ' - ' . get_sub_field('starting_id') + $amount - 1; } ?></td>
						<td title="Position"><?php the_sub_field('position'); ?></td>
						<td title="Addresses" colspan="2"><?php echo $sa; ?><?php if ($amount && $chs ) : for ( $i = 1; $i < $amount; $i++ ) { echo ', ' . $sa + ($i * $chs); } endif; ?></td>
					</tr>
				<?php endwhile; ?>
			<?php endif; ?>

			<?php if ( get_row_index() < count( $fixtures ) ) : ?>
				<tr><td colspan="6" style="border-left:0;border-right:0;border-bottom:0;padding:2rem;"></td></tr>
			<?php endif; ?>

		<?php endwhile; ?>
	</table>

	<style>
		table.fixtures {
			width: 100%;
			border-collapse: collapse;
		}

		table.fixtures td {
			padding: 0.5rem 1rem;
			border: 2px solid #222;
		}
	</style>
<?php endif; ?>
