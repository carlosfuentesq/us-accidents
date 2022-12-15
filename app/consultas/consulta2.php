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
                <th> Ano </th>
                <th> Cantidad de Accidentes</th>
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
	   $variable2 = $_GET['input2'];		   
           $stmt = $pdo->prepare('SELECT year, count
                                  FROM count_by_year
				  WHERE month = :valor1 AND day = :valor2');
	   $stmt->execute(['valor1' => $variable1,
	   		'valor2' => $variable2]);
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
   <div class="caption"><br>Accidentes totales ocurridos en la fecha <?php echo $variable2;?> - <?php echo $variable1;?> (DD-MM), agrupados por anos<br><br></div>
   <br>
   <a class="btn" href="https://grupo47.cc3201.dcc.uchile.cl">Volver</a>
</body>
</html>
