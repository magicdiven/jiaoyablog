<?php
/**
 * Created by PhpStorm.
 * User: dingxiaowang
 * Date: 2016/5/23
 * Time: 14:14
 */

namespace Home\Controller;

use Home\Model\Blog_commentModel;
use Home\Model\BlogModel;
use Home\Model\Master_blog_replyModel;
use Think\Controller;
use Think\Model;


class BlogController extends IndexController
{
    public function _initialize()
    {
        // utf-8编码
        header('Content-Type: text/html; charset=utf-8');
        layout('BlogCommon/bloglayout');//加载博客模板
        //实例化blog表模型
        $model = new BlogModel;
        //实例化blog_comment表模型
        $model_comment = new Blog_commentModel;
        //原创数量
        $original_article_num = $model->get_original_num();
        //转载数量
        $reprinted_article_num = $model->get_reprinted_num();
        //翻译数量
        $translation_article_num = $model->get_translation_num();
        //评论条数
        $comment_num = $model_comment->get_comment_num();
        //阅读排行，最多显示10篇
        $read_rank = $model->read_rank();
        //获取文章创建涉及的时间段，精确到月份
        $archives_date = $model->article_archives();
        //每个时间段有几篇文章
        $date_num = $model->get_date_num($archives_date);
        //每日推荐，最多推荐5篇
        $recommend_articles = $this->recommend_articles($read_rank);
        //评论排行,最多展示10篇
        $comment_articles = $model_comment->comment_articles();
        //blog_id 对应的文章标题
        $blog_id_to_title = $model->id_to_title();

        $this->assign('original_article_num', $original_article_num);
        $this->assign('reprinted_article_num', $reprinted_article_num);
        $this->assign('translation_article_num', $translation_article_num);
        $this->assign('comment_num', $comment_num);
        $this->assign('read_rank', $read_rank);
        $this->assign('archives_date', $archives_date);
        $this->assign('date_num', $date_num);
        $this->assign('recommend_articles', $recommend_articles);
        $this->assign('comment_articles', $comment_articles);
        $this->assign('blog_id_to_title', $blog_id_to_title);

    }

    /**
     * 博客主页
     */
    public function index()
    {
        //每页显示数
        $page_list_rows = C('PAGE_LIST_ROWS');
        //获取分页
        $pageNum = I('get.p', 1);
        $month = I('get.month');
        $article_name = I('get.articleName');
        $this->assign('articleName', $article_name);
        //实例化blog表模型
        $model = new BlogModel;
        //总行数
        //是否有文章存档条件
        if ($month) {
            $model = $model->where(array('created_at' => array(array('egt', strtotime($month)), array('lt', strtotime(date('Y-m-01', strtotime('+1 month', strtotime($month))))))));
        }
        //是否搜索文章
        if ($article_name) {
            $model = $model->where(array("title" => array("like", "%$article_name%")));
        }

        $totalRows = $model->field("count(*) as num")->find();

        // 实例化分页
        $page = new \Org\Util\Page($totalRows['num'], $page_list_rows);
        $pageoffset = ($pageNum - 1) * $page_list_rows;
        $result['show'] = $page->show();

        //是否有文章存档条件
        if ($month) {
            $model = $model->where(array('created_at' => array(array('egt', strtotime($month)), array('lt', strtotime(date('Y-m-01', strtotime('+1 month', strtotime($month))))))));
        }
        //是否搜索文章
        if ($article_name) {
            $model = $model->where(array("title" => array("like", "%$article_name%")));
        }
        //按分页条件获取文章
        $blogs = $model->limit($pageoffset, $page_list_rows)->order('id desc')->select();

        //赋值给前端
        $this->assign('page', $result['show']);
        $this->assign('blogs', $blogs);
        $this->assign('totalRows', $totalRows['num']);
        $this->display();
    }

    /**
     * 文章详情、评论列表、站长回复评论显示
     */
    public function article()
    {
        //每页显示数
        $page_list_rows = C('PAGE_LIST_ROWS');
        //获取分页数
        $pageNum = I('get.p', 1);
        $id = I('get.details');
        //实例化blog表模型
        //文章详情
        $model = new BlogModel;
        //博客评论表
        $model_comment = new Blog_commentModel;
        //站长评论
        $model_master = new Master_blog_replyModel;
        //当前文章
        $article_content = $model->where('id = ' . $id)->find();
        //上一篇
        $pre_id = $id - 1;
        while (null == ($pre_article = $model->where('id = ' . $pre_id)->find()) && $pre_id >= 0) {
            $pre_id--;
        }
        //下一篇
        $maxid = $model->max('id');
        $next_id = $id + 1;
        while (null == ($next_article = $model->where('id = ' . $next_id)->find()) && $next_id <= $maxid) {
            $next_id++;
        }
        //每次点击进来，就会使阅读次数加1；
        $save['read_times'] = $article_content['read_times'] + 1;
        $model->where('id = ' . $id)->save($save);
        //显示页面也增加1；,先不增加1,因为还是正在阅读,不是已阅读.
        //$article_content['read_times']++;

        //站长回复评论
        $master_reply = $model_master->where("blog_id =" . $id)->getField("comment_id, reply_content");
        //该文章所有评论数，用来分页；
        $total_comments = $model_comment->where("blog_id = ". $id)->field("COUNT(*) AS num")->find();
        // 实例化分页
        $page = new \Org\Util\Page($total_comments['num'], $page_list_rows);
        $pageoffset = ($pageNum - 1) * $page_list_rows;
        $result['show'] = $page->show();
        $comment_list = $model_comment->where("blog_id = ". $id)->order("id desc")->limit($pageoffset, $page_list_rows)->select();
        //评论楼层基数
        $floor_num = ($pageNum - 1) * $page_list_rows + 1;
        $this->assign('page', $result['show']);
        $this->assign('comment_list', $comment_list);
        $this->assign('master_reply', $master_reply);
        $this->assign('total_comments', $total_comments);
        $this->assign('article', $article_content);
        $this->assign('pre_article', $pre_article);
        $this->assign('next_article', $next_article);
        $this->assign('comment_list', $comment_list);
        $this->assign('floor_num', $floor_num);
        $this->display();
    }

    /**
     * 保存评论
     */
    public function comment()
    {
        $model = new Blog_commentModel;
        $post = I('post.');
        if (!check_verify_code($post['verify_code'])) {
            $this->redirect("article?details={$post['blog_id']}&error_msg_verifycode=验证码错误！#dump_to_comment");
        }
        if ( $post['nick'] && strlen($post['nick']) <= 20 ) {
            $save['nick'] = $post['nick'];
            if ( $post['comment'] && strlen($post['comment'] <= 255 )) {
                $save['comment'] = $post['comment'];
                if ($post['blog_id']) {
                    $save['blog_id'] = $post['blog_id'];
                    $save['created_at'] = time();
                    $save['ip_addr'] = get_client_ip();
                    //保存
                    $model->add($save);
                    $this->redirect("article?details={$post['blog_id']}#commentList");
                }
            } else {
                $this->redirect("article?details={$post['blog_id']}&error_msg_comment=未写评论或者评论太长！#dump_to_comment");

            }
        } else {
            $this->redirect("article?details={$post['blog_id']}&error_msg_nick=未写昵称或者昵称太长！#dump_to_comment");
        }
    }

    /**
     * 保存站长回复给游客的评论
     * 需要字段： comment_id, reply_content, blog_id
     */
    public function master_reply()
    {
        $model = new Master_blog_replyModel;
        $data = I('post.');
        if ($data['blog_id']) {
            $save['blog_id'] = $data['blog_id'];
            if ($data['comment_id']) {
                $save['comment_id'] = $data['comment_id'];
                if ( $data['reply_content'] && strlen($data['reply_content']) <= 255) {
                    $save['reply_content'] = $data['reply_content'];
                    $save['created_at'] = time();
                    $save['ip_addr'] = get_client_ip();
                    //保存
                    $model->add($save);
                    $this->redirect("article?details={$data['blog_id']}");
                }
            }
        }
        $this->redirect("article?details={$data['blog_id']}");
    }

    /**
     * 每日推荐
     * 算法：
     * 1、按照阅读排行前后顺序，根据星期几对应的数字，来显示排名第几的倍数的文章。最多显示5篇
     * 2、这里只需要获取阅读排行数组的key,在前端读取阅读排行数组$read_rank;
     */
    public function recommend_articles($read_rank)
    {
        //今天是每一周的第几天： 1-7：周一~周日
        $day_num = date('w', time()) + 1;
        //阅读排行中文章总数==所有文章总数
        $article_num = count($read_rank);
        if ($article_num > 5) {
            //获取每日推荐的文章
            $recommend_articles = array();
            $recommend_num = 0;
            foreach ($read_rank as $key => $read) {
                //取 $key+1 % $day_num == 0 的文章
                $mod_num = ($key + 1) % $day_num;
                switch ($mod_num) {
                    case 0:
                        $recommend_articles[] = $key;
                        $recommend_num++;
                        break;
                }
                if (5 <= $recommend_num) {
                    break;
                }
            }
        } else {
            foreach ($read_rank as $key => $read) {
                $recommend_articles[] = $key;
            }
        }
        return $recommend_articles;
    }

    /**
     * 验证码图片,发评论时需要验证码
     */
    public function verifyCode() {
        $config = array(
            'imageW' => 120,
            'imageH' => 30,
            'fontSize' => 15, // 验证码字体大小
            'length' => 4, // 验证码位数
            'useNoise' => false, // 关闭验证码杂点
            'codeSet' => '0123456789'
        );
        create_verify_code($config);
    }
}