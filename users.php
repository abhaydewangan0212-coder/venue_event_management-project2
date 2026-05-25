<?php
include 'db.php';
$result = mysqli_query($conn, "SELECT * FROM users");
?>
<?php
// Delete user
if (isset($_GET['delete_user'])) {
    $uid = (int) $_GET['delete_user'];

    if ($uid > 0) {
        if ($conn->query("DELETE FROM users WHERE id = $uid")) {
            echo "<div class='alert alert-success'>User deleted successfully.</div>";
        } else {
            echo "<div class='alert alert-danger'>Error deleting user.</div>";
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Registered Users</title>
    <style>
        body {
            font-family: Arial;
            margin: 20px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            background: #fff;
        }
        th, td {
            padding: 10px;
            border: 1px solid #ccc;
            text-align: left;
        }
        th {
            background: #007bff;
            color: white;
        }
        tr:nth-child(even) {
            background: #f9f9f9;
        }
    </style>
</head>
<body>

<h2>All Registered Users</h2>

<table>
    <tr>
        <th>ID</th>
        <th>Name</th>
        <th>Email</th>
        <th>Registered On</th>
        <th>Contact</th>
        <th>Delete</th>
    </tr>

    <?php while($row = mysqli_fetch_assoc($result)) { ?>
    <tr>
        <td><?= $row['id'] ?></td>
        <td><?= $row['name'] ?></td>
        <td><?= $row['email'] ?></td>
        
        <td><?= $row['created_at'] ?></td>
        <td>
            <a href="tel:<?php echo $row['contact'] ?>">
                <?=$row['contact']?></td>
        
        <td>
  <a href="?tab=users&delete_user=<?= $row['id'] ?>"
     onclick="return confirm('Are you sure you want to delete this user?');"
     style="color:red;">
     Delete
  </a>
</td>
    </tr>
    <?php } ?>

</table>

</body>
</html>