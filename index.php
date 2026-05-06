<!DOCTYPE html>
<html>
<head>
<title>Akreditasi Pro</title>
<link rel="stylesheet" href="style.css">
</head>

<body>

<div class="container">

<!-- SIDEBAR -->
<div class="sidebar">

<h2>Akreditasi Pro</h2>

<ul>

<li>
<a href="index.php?page=dashboard">Dashboard</a>
</li>

<li>
<a href="index.php?page=borang">Borang</a>
</li>

<li>
<a href="index.php?page=tim">Tim Akreditasi</a>
</li>

<li>
<a href="index.php?page=dokumen">Bukti Dokumen</a>
</li>

</ul>

</div>


<!-- KONTEN KANAN -->
<div class="content">

<?php

$page = $_GET['page'] ?? 'dashboard';

if($page == "dashboard"){
    include "dashboard.php";
}

elseif($page == "borang"){
    include "borang.php";
}

elseif($page == "tim"){
    include "tim.php";
}

elseif($page == "dokumen"){
    include "dokumen.php";
}

else{
    include "dashboard.php";
}

?>

</div>

</div>

</body>
</html>