<?php

namespace App\Http\Controllers;

use App\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Crypt;

class UserController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    public function store(Request $request)
    {

        $this->validate(
            $request,
            [
                "name" => "required|max:255",
                "email" => "required|max:255|unique:users",
                "password" => "required|confirmed|max:255"
            ]
        );
        $user = new User($request->all());
        $user->password = Crypt::encrypt($request->input("password"));
        echo ("aqui");
        echo ($user->password);
        $user->api_token = str_random(60);
        $user->save();
        return $user;
    }

    public function login(Request $request)
    {
        $dados = $request->only('email', 'password');
        $user = User::where("email", $dados['email'])
            ->first();

        if (Crypt::decrypt($user->password) == $dados['password']) {
            $user->api_token = str_random(60);
            $user->update();
            return ["api_token" => $user->api_token];
        }

        return new Response('LOGIN OU USUÁRIO INVÁLIDO');
    }

    public function view($id)
    {
        return User::find($id);
    }

    public function delete($id)
    {
        if (User::destroy($id)) {
            return new Response("Removido com sucesso", 200);
        }

        return new Response("Não foi possível remover usuário", 401);
    }

    public function list()
    {
        return User::all();
    }

    public function update(Request $request, $id)
    {
        $dadosValidacao = [
            "name" => "required|max:255",
            "email" => "required|max:255|unique:users"
        ];

        if (isset($request->all()['password'])) {
            $dadosValidacao["password"] = "required|max:255|confirmed";
        }

        $this->validate(
            $request,
            $dadosValidacao
        );

        $user = User::find($id);
        $user->name = $request->input("name");
        $user->email = $request->input("email");

        if (isset($request->all()['password'])) {
            $user->password = Crypt::encrypt($request->input("password"));
        }

        $user->update();
        return $user;
    }

    //
}
