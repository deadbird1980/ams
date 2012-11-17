<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
    <head>
        <?php include Doo::conf()->SITE_PATH .  Doo::conf()->PROTECTED_FOLDER . "viewc//header.php"; ?>
    </head>
	<body>
        <?php if( isset($data['users']) && !empty($data['users']) ): ?>
        <table>
            <tbody>

                <tr>
                <th># </th>
                <th>UserName </th>
                <th>Email </th>
                <th>Mobile </th>
                </tr>
                <?php foreach($data['users'] as $k1=>$v1): ?>
                <tr>
                <td><?php echo $v1->id; ?></td>
                <td><?php echo $v1->username; ?></td>
                <td><?php echo $v1->email; ?></td>
                <td><?php echo $v1->email; ?></td>
                </tr>
                <?php endforeach; ?>

        </tbody></table>
        <?php endif; ?>

	</body>
</html>
