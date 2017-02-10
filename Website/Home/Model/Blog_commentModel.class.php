<?php
/**
 * Created by PhpStorm.
 * User: 60005
 * Date: 2016/5/27
 * Time: 16:47
 */

namespace Home\Model;


use Think\Model;

class Blog_commentModel extends Model
{

    /**
     * 获取所有文章评论的总数量
     * @return mixed
     */
    public function get_comment_num()
    {
        return M("Blog_comment")->getField("COUNT(*) AS num");
    }

    /**
     *
     * 获取每篇文章的评论
     * @return mixed
     */
    public function comment_articles()
    {
        return M("Blog_comment")->group("blog_id")->getField("blog_id, COUNT(blog_id) AS comment_num");
    }
}