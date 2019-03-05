<?php
// $Id: modules.php 965 2012-05-28 03:18:09Z i.bitcero $
// --------------------------------------------------------------
// Red México Common Utilities
// A framework for Red México Modules
// Author: Eduardo Cortés <i.bitcero@gmail.com>
// Email: i.bitcero@gmail.com
// License: GPL 2.0
// --------------------------------------------------------------

class RMFtpClient
{
    private $server = '';
    private $port   = 21;
    private $uname  = '';
    private $pass   = '';
    // Almacena el manejador ftp
    private $connection = '';

    /**
     * @desc Constructor de la clase
     * @param string $server Nombre del servidor al que se conectará
     * @param int $port Número de puerto al que se conectará
     * @param string $uname Nombre de usuario
     * @param string $pass Contraseña de conexión
     */
    public function __construct($server, $port = 21, $uname = '', $pass = '')
    {
        $this->server = $server;
        $this->port   = $port;
        $this->uname  = $uname;
        $this->pass   = $pass;
    }

    /**
     * @desc Coneción al servidor
     * @param mixed $ssl
     * @param mixed $timeout
     * @return bool
     */
    public function connect($ssl = false, $timeout = 90)
    {
        $this->connection = $ssl ? ftp_ssl_connect($this->server, $this->port, $timeout) : ftp_connect($this->server, $this->port, $timeout);
        if (!ftp_login($this->connection, $this->uname, $this->pass)) {
            return false;
        }

        return true;
    }

    /**
     * @desc Activa el modo pasivo
     * @param bool $bool Activar o desactivar
     */
    public function pasv($bool = true)
    {
        ftp_pasv($this->connection, $bool);
    }

    /**
     * @desc Directorio Actual
     * @return string Directorio Actual
     */
    public function pwd()
    {
        return ftp_pwd($this->connection);
    }

    /**
     * @desc Cambia el directorio actual
     * @param string $dir Directorio existente en el servidor
     * return bool
     */
    public function chdir($dir)
    {
        return ftp_chdir($this->connection, $dir);
    }

    /**
     * @desc Elimina un archivo del servidor
     * @param string $ruta Ruta existente del archivo
     * @return bool
     */
    public function delete($ruta)
    {
        return ftp_delete($this->connection, $ruta);
    }

    /**
     * @desc Establece los permisos de un archivo en el servidor
     * @param mixed $mode Modo del permiso cono un numero octal(Ej. 0644)
     * @param string $ruta Ruta del archivo
     * @return bool o octal
     */
    public function chmod($mode, $ruta)
    {
        return ftp_chmod($this->connection, (int)$mode, $ruta);
    }

    /**
     * @desc Obtiene un archivo desde el servidor FTP y lo almacena localmente
     * @param string $local Ruta a un archivo local
     * @param string $remote Ruta del archivo en el servidor (debe existir)
     * @param string $mode Modo de tansferencia (FTP_ASCII o FTP_BINARY
     * @param int $pos Desde donde se empieza a descargar el archivo
     * @return bool
     */
    public function get($local, $remote, $mode = FTP_ASCII, $pos = 0)
    {
        return ftp_get($this->connection, $local, $remote, $mode, $pos);
    }

    /**
     * @desc Devuelve la fecha de modificación de un archivo en el servidor
     * @param string $ruta Ruta válida del archivo
     * @return int
     */
    public function mtime($ruta)
    {
        return ftp_mdtm($this->connection, $ruta);
    }

    /**
     * @desc Crea un directorio en el servidor
     * @param string $dir Directorio
     * @param mixed
     * @return string|false
     */
    public function mkdir($dir)
    {
        return ftp_mkdir($this->connection, $dir);
    }

    /**
     * @desc Lista de archivos
     * @param string $dir Directorio
     * @return array|false
     */
    public function nlist($dir)
    {
        return ftp_nlist($this->connection, $dir);
    }

    /**
     * @desc Carga un archivo al servidor FTP
     * @param string $remote Archivo remoto
     * @param string $local Archivo local
     * @param string $mode Modo de transferencia (FTP_ASCII o FTP_BINARY)
     * @param int $pos Posición de inicio
     * return bool
     */
    public function put($remote, $local, $mode = FTP_ASCII, $pos = 0)
    {
        return ftp_put($this->connection, $remote, $local, $mode, $pos);
    }

    /**
     * @desc Obtiene la lista de directorios archivos
     * @param string $dir Directorio
     * @param bool $mode Modo recursivo
     * @return array
     */
    public function rawlist($dir, $mode = false)
    {
        return ftp_rawlist($this->connection, $dir, $mode);
    }

    /**
     * @desc Renombra un archivo o un directorio
     * @param string $old Nombre actual
     * @param string $new Nuevo nombre
     * @return bool
     */
    public function rename($old, $new)
    {
        return ftp_rename($this->connection, $old, $new);
    }

    /**
     * @desc Elimina un directorio
     * @param string $dir ruta al directorio
     * @return bool
     */
    public function rmdir($dir)
    {
        return ftp_rmdir($this->connection, $dir);
    }

    /**
     * @desc Obtiene el tamaño de un archivo remoto
     * @param string $file Ruta del archivo
     * @return int
     */
    public function size($file)
    {
        return ftp_size($this->connection, $file);
    }

    public function systype()
    {
        return ftp_systype($this->connection);
    }

    /**
     * @desc Permite saber si un directorio existe
     * @param string $dir Directorio a comprobar
     * @return bool
     */
    public function isDir($dir)
    {
        $current = $this->pwd();
        if ($this->chdir($dir)) {
            $this->chdir($current);

            return true;
        }

        return false;
    }

    /**
     * @desc Cierra la conexión FTP
     */
    public function close()
    {
        ftp_close($this->connection);
    }
}
