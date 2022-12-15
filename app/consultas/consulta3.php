<!DOCTYPE html>
<html>
    <head>
	<script src="http://html5shiv.googlecode.com/svn/trunk/html5.js"></script>
	<link rel="stylesheet" href="../style.css">
    </head>
<body>
    <?php
        echo "<table>";
        echo "<tr>
        	 <th> ID </th>
		 <th> Description </th>
		 <th> Severity </th>
		 <th> Start Time </th>
		 <th> Start Lat </th>
		 <th> Start Long </th>
		 <th> Distance </th>
              </tr>";

        class TableRows extends RecursiveIteratorIterator {
            function __construct($it) {
                parent::__construct($it, self::LEAVES_ONLY);
            }
            function current() {
                return "<td>" . parent::current(). "</td>";
            }
            function beginChildren() {
                echo "<tr>";
            }
            function endChildren() {
                echo "</tr>" . "\n";
            }
        }

        try {
           $pdo = new PDO('pgsql:
                           host=localhost;
                           port=5432;
                           dbname=cc3201;
                           user=cc3201;
                           password=hoste.aciano.rodas.arre');
           $variable1 = $_GET['input1'];
           $stmt = $pdo->prepare('SELECT *
                                  FROM accident
				  WHERE id IN (
					SELECT accident_id
					FROM accident_nearbyelement
	   				WHERE nearby_element_id = :valor1)');
           $stmt->execute(['valor1' => intval($variable1)]);
           $result = $stmt->setFetchMode(PDO::FETCH_ASSOC);

           foreach(new TableRows(new RecursiveArrayIterator($stmt->fetchAll())) as $k=>$v) {
               echo $v;
           }
        }
        catch(PDOException $e){
            echo $e->getMessage();
        }
   ?>
   <br>
   <div class="caption"><br>Accidentes que poseen el elemento cercano seleccionado<br><br></div>
   <br>
   <a class="btn" href="https://grupo47.cc3201.dcc.uchile.cl">Volver</a>
</body>
</html>
