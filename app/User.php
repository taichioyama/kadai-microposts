<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];
    
    public function microposts()
    {
        return $this->hasMany(Micropost::class);
    }
    
    public function followings()
    {
        return $this->belongsToMany(User::class, 'user_follow', 'user_id', 'follow_id')->withTimestamps();
    }
    
    public function followers()
    {
        return $this->belongsToMany(User::class, 'user_follow', 'follow_id', 'user_id')->withTimestamps();
    }
    
    public function follow($userId)
    {
        $exist = $this->is_following($userId);  //すでにフォローしているか
        $its_me =$this->id == $userId;  //相手が自分自身でないか
        
        if ($exist || $its_me) {
            return false;
        } else {
            $this->followings()->attach($userId);
            return true;
        }
    }
    
    public function unfollow($userId)
    {
        $exist = $this->is_following($userId);
        $its_me = $this->id == $userId;
        
        if ($exist && !$its_me)
        {
            $this->followings()->detach($userId);
            return true;
        } else {
            return false;
        }
    }
    
    //すでにフォローしているか
    public function is_following($userId)
    {
        return $this->followings()->where('follow_id', $userId)->exists();
    }
    
    //フォローしているユーザーと自分の投稿の一覧取得
    public function feed_microposts()
    {
        $follow_user_ids = $this->followings()->pluck('users.id')->toArray();
        $follow_user_ids[] = $this->id;
        return Micropost::whereIn('user_id', $follow_user_ids);
    }
    
    //---------------------- ここからお気に入り ----------------
    
    //お気に入り一覧取得機能
    public function favorites()
    {
        return  $this->belongsToMany(Micropost::class, 'favorites', 'user_id', 'micropost_id')->withTimestamps();
    }
    
    //お気に入り追加機能 
    public function favorite($micropostsId)
    {
        $exist = $this->is_favorite($micropostsId); 
        
        if ($exist) {
            return false;
        } else {
            $this->favorites()->attach($micropostsId);
            return true;
        }
    }
    
    //お気に入り削除機能
    public function unfavorite($micropostsId)
    {
        $exist = $this->is_favorite($micropostsId);
        
        if ($exist) {
            $this->favorites()->detach($micropostsId);
            return true;
        } else {
            return false;
        }
    }
    
    //すでにお気に入りに追加されているか
    public function is_favorite($micropostsId)
    {
        return $this->favorites()->where('micropost_id', $micropostsId)->exists();
    }
    
}
