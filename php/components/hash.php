<?php
function hashsenha(string $senha): string
{
    $salt = "libera o Endrick!";
    $salteado = $senha . $salt;
    return hash('sha256', $salteado);
}
