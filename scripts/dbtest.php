<?php

$host = "192.168.100.20";
$port = "5432";
$dbname = "webappdb";
$user = "webuser";
$password = "CHANGE_ME";

$conn = pg_connect(
    "host=$host port=$port dbname=$dbname user=$user password=$password"
);

if (!$conn) {
    die("Database connection failed.");
}

$result = pg_query(
    $conn,
    "SELECT id, name, department FROM employees ORDER BY id"
);

echo "<h1>Employee List</h1>";
echo "<table border='1'>";
echo "<tr><th>ID</th><th>Name</th><th>Department</th></tr>";

while ($row = pg_fetch_assoc($result)) {
    echo "<tr>";
    echo "<td>" . htmlspecialchars($row["id"]) . "</td>";
    echo "<td>" . htmlspecialchars($row["name"]) . "</td>";
    echo "<td>" . htmlspecialchars($row["department"]) . "</td>";
    echo "</tr>";
}

echo "</table>";

pg_close($conn);
?>