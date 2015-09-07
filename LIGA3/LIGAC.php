<?php
 class LIGAC extends LIGA {
    private $llave;
    function __construct($s, $q='', $l='') {
        $this->llave = $s.(is_array($q) ? serialize($q) : $q ).$l;
        parent::__construct($s, $q, $l);
    }
    function meta($f=false) {
        //$this->bd = BD();
        if (count($this->meta) === 0 || $f) {
            if (apc_exists('meta'.$this->llave)) {
                $this->meta = apc_fetch('meta'.$this->llave);
            } else {
                apc_store('meta'.$this->llave, parent::meta($f));
            }
        }
        return parent::meta($f);
    }
    function info($f=false) {
        //$this->bd = BD();
        if (count($this->info) === 0 || $f) {
            if (apc_exists('info'.$this->llave)) {
                $this->info = apc_fetch('info'.$this->llave);
            } else {
                apc_store('info'.$this->llave, parent::info($f));
            }
        }
        return parent::info($f);
    }
    function actualizar() {
        // APC
        apc_delete($this->llave);
        parent::actualizar();
    }
    function limpiar_cache() {
        // APC
        apc_clear_cache();
        apc_clear_cache('user');
    }
 }
?>