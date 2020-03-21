<?php

namespace Kneub\Model;

use Kneub\Model\User;

class UserRepository extends User
{

    public function getAll()
    {
        return User::orderBy('login', 'asc')->get();
    }

    public function getById($id)
    {
        return User::find($id);
    }

    public function getByLogin($login)
    {
        return User::where('login', $login)
            ->where('suspendre', false)
            ->first();
    }

    public function getBySearch($search, int $page = 1, $pagination = 10)
    {

        // without search
        if(empty($search)){
          $count = User::count();
          $offset = ($page-1) * $pagination;

          $users = User::skip($offset)->take($pagination)->orderBy('login', 'asc')->get();
          return ['list' => $users, 'nbPages' => ceil($count / $pagination),'nbTotal' => $count, 'itemsByPage' => $pagination];
        }

        // search by login or role
        $count = User::where('login','ilike',"%$search%")
            ->orWhere('role',$search)->count();
        $offset = ($page-1)*$pagination;

        $users = User::where('login','ilike',"%$search%")
            ->orWhere('role',$search)->skip($offset)->take($pagination)->orderBy('login', 'asc')->get();
        return ['list' => $users, 'nbPages' => ceil($count/$pagination),'nbTotal' => $count, 'itemsByPage' => $pagination];

    }
}
