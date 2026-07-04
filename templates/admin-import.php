<?php
// Admin Import Shows page
$import_admin = new Show_Submissions_Import_Admin();
$endpoints = $import_admin->get_endpoints();
?>
<div class="wrap">
    <h1>Import Shows</h1>

    <h2>Add New API Endpoint</h2>
    <form method="post" action="">
        <table class="form-table">
            <tr valign="top">
                <th scope="row">Name</th>
                <td><input type="text" name="name" value=""/></td>
            </tr>
            <tr valign="top">
                <th scope="row">API URL</th>
                <td><input type="text" name="api_url" value="" class="regular-text"/></td>
            </tr>
            <tr valign="top">
                <th scope="row">Frequency (in minutes)</th>
                <td><input type="number" name="frequency" value="1440"/></td>
            </tr>
            <tr valign="top">
                <th scope="row">Status</th>
                <td>
                    <select name="status">
                        <option value="Active">Active</option>
                        <option value="Inactive" selected>Inactive</option>
                    </select>
                </td>
            </tr>
        </table>
        <input type="hidden" name="action" value="add_endpoint">
        <?php submit_button('Add Endpoint'); ?>
    </form>

    <h2>Existing Endpoints</h2>
    <table class="wp-list-table widefat fixed striped">
        <thead>
            <tr>
                <th>Name</th>
                <th>API URL</th>
                <th>Frequency</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if ( $endpoints ) : ?>
                <?php foreach ( $endpoints as $endpoint ) : ?>
                    <tr>
                        <td><?php echo esc_html( $endpoint->name ); ?></td>
                        <td><?php echo esc_url( $endpoint->api_url ); ?></td>
                        <td><?php echo intval( $endpoint->frequency ); ?></td>
                        <td><?php echo esc_html( $endpoint->status ); ?></td>
                        <td>
                            <a href="?page=show-submissions-import&action=delete_endpoint&id=<?php echo $endpoint->id; ?>">Delete</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else : ?>
                <tr class="no-items">
                    <td class="colspanchange" colspan="5">No endpoints found.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>
