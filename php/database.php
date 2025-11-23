<?php
//Clase que se encarga de manejar la conexión de la base de datos MySQL
//Mediante PDO(PHP Data Objects)
class Database
{   //Recomendaciones
    #No usaar root en producción, usar un usuario con permisos específicos
    #No dejar la contraseña vacía, usar clave segura    W8e:c/B?6InlxP?}

    public static function conectar()
    {
        #Se definen las constantes para la conexion

        $servidor = getenv('DB_HOST') !== false ? getenv('DB_HOST') : 'localhost';
        $nombre_bd = getenv('DB_NAME') !== false ? getenv('DB_NAME') : 'bd_electronic';
        $usuario = getenv('DB_USER') !== false ? getenv('DB_USER') : 'root';
        $password = getenv('DB_PASS') !== false ? getenv('DB_PASS') : 'root';
        
        //Opciones de configuración para PDO
        #Al configurar UtF-8 se asegura que los caracteres especiales se manejen correctamente en la BD
        $opciones = array(PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8');

        //Establecimiento de la conexión con PDO
        try {
            #Se crea una instancia de PDO, que establece conexion con MySQL
            $conexion = new PDO("mysql:host=" . $servidor . ";dbname=" . $nombre_bd, $usuario, $password, $opciones);
            #Se activa el modo de errores de con excepciones
            $conexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            #Desactiva la emulación de consultas preparadas, mejora la seguridad
            #contra inyecciones SQL
            $conexion->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
            #Si la conexión es exitosa, metodo devuelve el objeto PDO
            #que puede ser usado para ejecutar consultas desde cualquier parte del codigo
            return $conexion;
        } catch (Exception $e) {
            die("El error de conexión es: " . $e->getMessage());
        }
    }
}
