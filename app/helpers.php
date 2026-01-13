<?php

function user_login()
{
    $user_login = request()->user_login;
    return $user_login ? json_decode(json_encode($user_login)) : null;
}

function user_login_data($user_login = null)
{
    $user_login = $user_login ? json_decode(json_encode($user_login)) : user_login();
    return $user_login ? $user_login->user : null;
}

function user_login_roles($user_login = null)
{
    $user_login = $user_login ?: user_login();
    return $user_login ? $user_login->roles : [];
}

function user_login_permissions($user_login = null)
{
    $user_login = $user_login ?: user_login();
    return $user_login ? $user_login->permissions : [];
}

function user_login_satker($user_login = null)
{
    $user_login = $user_login ? json_decode(json_encode($user_login)) : user_login();
    $user_login_data = user_login_data($user_login);
    return $user_login_data ? $user_login_data->rl_satker : null;
}

function user_login_npp($user_login = null)
{
    $user_login = $user_login ? json_decode(json_encode($user_login)) : user_login();
    $user_login_data = user_login_data($user_login);
    return $user_login_data ? $user_login_data->npp : null;
}

function is_user_login_pti($user_login = null)
{
    return !empty(array_intersect(["developer", "superadmin", "admin-pti"], user_login_roles($user_login)));
}

function format_currency(float $num, int $decimals = 0)
{
    return number_format($num, $decimals, ',', '.');
}
