<?php
class Servicio {
    public $orden;
    public $fecha;
    public $cliente;
    public $tecnico;
    public $descripcion;
    public $importe;

    public function __construct($orden, $fecha, $cliente, $tecnico, $descripcion, $importe) {
        $this->orden = $orden;
        $this->fecha = $fecha;
        $this->cliente = $cliente;
        $this->tecnico = $tecnico;
        $this->descripcion = $descripcion;
        $this->importe = $importe;
    }
}
?>
