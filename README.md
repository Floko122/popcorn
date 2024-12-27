
# Popcorn
Tool for [popcorn initiative](https://theangrygm.com/popcorn-initiative-a-great-way-to-adjust-dd-and-pathfinder-initiative-with-a-stupid-name/) with admin backend.

Admins can create groups of entities (using only names) and create encounters in the backend.
On the encounter page the admin can add groups to the encounter. State changes are tracked between all viewers.

# Installation
<ol>
<li>
Copy the content from the source folder to your target location
e.g. /var/www/vhosts/your.domain/
</li>
<li>
Set up a database on the server and create an example user.
</li>
<li>
Run the db/init.sql on the database
</li>
<li>
Edit config.php in the src folder accordingly. 

````
$host = "localhost";
$dbname = "dbname";
$username = "username";
$password = "password";
````
</li>
</ol>

# Requirements
Developed with php 8.0.3 and MySQL 9.1.0
Should work with older versions. Let me know if you run into issues.

# Try/Use for [free](https://uni.datafloko.de/popcorn/)
