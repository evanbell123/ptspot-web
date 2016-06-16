<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 *
 * @author ebbmf
 */
interface AccountInterface {
    public function register($firstName, $lastName, $email, $password, $confirmPassword, $birthDate, $gender, $role);
}
