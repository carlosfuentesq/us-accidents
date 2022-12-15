<!DOCTYPE html>
<html>
    <head>
        <script src="http://html5shiv.googlecode.com/svn/trunk/html5.js"></script>
	<link rel="stylesheet" href="../style.css">
   </head>
<body>
    <?php
        # Esta sección corresponde al header de la tabla, deben agregar o quitar columnas 
        # para que calce con la consulta que quieren mostrar.
        # Tambien puede cambiar el nombre, por ej en vez de "Columna 4" puede ser "Nombre" o "Dirección", etc.
	echo "<table>";
        echo "<tr>
                <th> Calle </th>
                <th> Cantidad Total de Accidentes </th>
              </tr>";

        # Esta clase no la deben tocar, esta es la que permite que los datos se muestren como tabla.
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

        # Esta sección es la que se conecta a la base de datos,
        # prepara y ejecuta la consulta e imprime los resultados.
        try {
            # Acá en el PDO lo único que tienen que cambiar es la contraseña.
            # El PDO es lo que permite que no se puedan hacer inyecciones de SQL.
            $pdo = new PDO('pgsql:
                            host=localhost;
                            port=5432;
                            dbname=cc3201;
                            user=cc3201;
                            password=hoste.aciano.rodas.arre');
            # En esta sección obtenemos los inputs del html donde está el formulario con los inputs.
            # En este caso, el formulario para esta consulta tenía un input con name="input1".
            # Ustedes pueden agregar la cantidad de inputs que estimen necesario.
            # Por ejemplo, si agregan un input con name="edad", acá deberían hacer una variable con get a 'edad'.
	    $variable1=$_GET['input1'];
	    # En la siguiente sección se prepara la consulta para evitar inyecciones SQL, deben poner la consulta que desean ejecutar,
            # y dónde quieran poner un input deben poner un placerholder con dos puntos seguidos de un nombre que debe ser único dentro de la consulta.
            # Por ejemplo, en la siguiente consulta se usa el placeholder :valor1, pero esto podría ser :edad o :direccion o lo que prefieran.
	    if ($variable1 != ''){
		    $stmt = $pdo->prepare('SELECT l_street, total_accident 
			    	FROM top_streets
				WHERE l_city = :valor1');
		$stmt->execute(['valor1'=> $variable1]);
	    }
	    else{
		$stmt = $pdo->prepare('SELECT l_street, COUNT(l_street) as total_accident
				FROM us_accident.accident_location			
				GROUP BY l_city, l_street
				ORDER BY total_accident DESC');
		$stmt->execute();
	    }
            # Luego se obtienen los resultados.
            $result = $stmt->setFetchMode(PDO::FETCH_ASSOC);

            # Y se imprimen.
            foreach(new TableRows(new RecursiveArrayIterator($stmt->fetchAll())) as $k=>$v) {
                echo $v;
            }
        }
        # Esta sección imprime un error en caso de haber uno.
        catch(PDOException $e){
            echo $e->getMessage();
        }	
    ?>
	<br>
	<div class="caption"><br>Accidentes totales en la ciudad de "<?php echo $variable1; ?>", agrupados por el nombre de las calles<br><br></div>
	<br>
	<a class="btn" href="https://grupo47.cc3201.dcc.uchile.cl">Volver</a>
</body>
</html>
