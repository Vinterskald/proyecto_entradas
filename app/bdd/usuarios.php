<?php
    //Clase usuarios; modelo usando objetos de tipo usuario
    class usuarios{
        //Atributos de usuario:
        private $id;
        private $contra;
        private $nombre;
        private $apellidos;
        private $cuenta;
        private $correo;
        private $direccion;
        //------------------------------------------
        //Constructor:
        public function __construct(array $datos){
            $this->id = $datos[0];
            $this->contra = $datos[1];
            $this->nombre = $datos[2];
            $this->apellidos = $datos[3];
            $this->cuenta = $datos[4];
            $this->correo = $datos[5];
            $this->direccion = $datos[6];
        }
        //Getter y setter manuales:
        
        //Getters:
        public function getId(){
            return $this->id;
        }
        public function getContra(){
            return $this->contra;
        }
        public function getNombre(){
            return $this->nombre;
        }
        public function getApellidos(){
            return $this->apellidos;
        }
        public function getCuenta(){
            return $this->cuenta;
        }
        public function getCorreo(){
            return $this->correo;
        }
        public function getDireccion(){
            return $this->direccion;
        }
        
        //Setters:
        public function setContra($contra){
            $this->contra = $contra;
        }
        public function setNombre($nombre){
            $this->nombre = $nombre;
        }
        public function setApellidos($apell){
            $this->apellidos = $apell;
        }
        public function setCuenta($user){
            $this->cuenta = $user;
        }
        public function setCorreo($correo){
            $this->correo = $correo;
        }
        public function setDireccion($direc){
            $this->direccion = $direc;
        }
        //------------------------------------------------------------------------------------------------------------------
    }
?>