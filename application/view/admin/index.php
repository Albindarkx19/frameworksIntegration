<div class="container">
    <h1>Admin/index</h1>

    <div class="box">

        <!-- echo out the system feedback (error and success messages) -->
        <?php $this->renderFeedbackMessages(); ?>

        <h3>What happens here ?</h3>

        <div>
            This controller/action/view shows a list of all users in the system. with the ability to soft delete a user
            or suspend a user.
        </div>
        <div>
            <table class="overview-table">
                <thead>
                <tr>
                    <td>Id</td>
                    <td>Avatar</td>
                    <td>Username</td>
                    <td>User's email</td>
                    <td>Activated ?</td>
                    <td>Link to user's profile</td>
                    <td>suspension Time in days</td>
                    <td>Soft delete</td>
                    <td>Submit</td>
                </tr>
                </thead>
                <?php foreach ($this->users as $user) { ?>
                    <tr class="<?= ($user->user_active == 0 ? 'inactive' : 'active'); ?>">
                        <form action="<?= config::get("URL"); ?>admin/actionAccountSettings" method="post">
                            <td><?= $user->user_id; ?></td>
                            <td class="avatar">
                                <?php if (isset($user->user_avatar_link)) { ?>
                                    <img src="<?= $user->user_avatar_link; ?>"/>
                                <?php } ?>
                            </td>
                            <td><input type="text" name="user_name" value="<?= $user->user_name; ?>" /></td>
                            <td><input type="text" name="user_email" value="<?= $user->user_email; ?>" /></td>
                            <td><?= ($user->user_active == 0 ? 'No' : 'Yes'); ?></td>
                            <td>
                                <a href="<?= Config::get('URL') . 'profile/showProfile/' . $user->user_id; ?>">Profile</a>
                            </td>
                            <td><input type="number" name="suspension" value="<?= $user->user_suspension_time_in_days; ?>" /></td>
                            <td><input type="checkbox" name="softDelete" <?php if ($user->user_deleted) { ?> checked <?php } ?> /></td>
                            <td>
                                <input type="hidden" name="user_id" value="<?= $user->user_id; ?>" />
                                <input type="submit" value="Update" />
                            </td>
                        </form>
                    </tr>
                <?php } ?>
            </table>
        </div>
    </div>
</div>
