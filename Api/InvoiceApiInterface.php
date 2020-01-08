<?php

namespace MundiPagg\MundiPagg\Api;

interface InvoiceApiInterface
{
    /**
     * List invoice
     *
     * @return mixed
     */
    public function list($id);


    /**
     * Cancel invoice
     *
     * @param int $id
     * @return mixed
     */
    public function cancel($id);
}