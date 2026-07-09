<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

global $wpdb;
$table_name  = $wpdb->prefix . 'lhxc_show_submissions';
$submissions = $wpdb->get_results( $wpdb->prepare( 'SELECT * FROM %i ORDER BY created_at DESC', $table_name ) );
?>

<div class="wrap">
	<h1>Show Submissions</h1>

	<table class="wp-list-table widefat fixed striped">
		<thead>
			<tr>
				<th>Date Submitted</th>
				<th>Show Date</th>
				<th>Venue</th>
				<th>Performers</th>
				<th>Door Price</th>
				<th>Ticket Price</th>
				<th>Status</th>
				<th>Actions</th>
			</tr>
		</thead>
		<tbody>
			<?php foreach ( $submissions as $submission ) : ?>
				<tr data-id="<?php echo esc_attr( $submission->id ); ?>">
					<td><?php echo esc_html( gmdate( 'Y-m-d', strtotime( $submission->created_at ) ) ); ?></td>
					<td><?php echo esc_html( $submission->show_date ); ?></td>
					<td><?php echo esc_html( $submission->venue_name ); ?></td>
					<td><?php echo esc_html( $submission->performers ); ?></td>
					<td>
						<label class="switch">
							<input type="checkbox" class="approval-toggle"
									<?php echo $submission->approved ? 'checked' : ''; ?>>
							<span class="slider round"></span>
						</label>
					</td>
					<td>
						<button class="button view-details">View Details</button>
					</td>
				</tr>
			<?php endforeach; ?>
		</tbody>
	</table>

	<!-- Modal for submission details -->
	<div id="submissionModal" class="modal">
		<div class="modal-content">
			<span class="close">&times;</span>
			<div id="submissionDetails"></div>
		</div>
	</div>
</div>