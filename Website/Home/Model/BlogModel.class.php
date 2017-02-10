<?php

/**
 * Created by PhpStorm.
 * User: dingxiaowang
 * Date: 2016/5/24
 * Time: 16:13
 */
namespace Home\Model;
use Think\Model;

class BlogModel extends Model
{

    /**
     * 获取原创文章的数量
     * @return mixed
     */
    public function get_original_num()
    {
        return M('Blog')->where('article_type = "原创"')->getField('COUNT(*) AS num');
    }

    /**
     * 获取转载文章的数量
     * @return mixed
     */
    public function get_reprinted_num()
    {
        return M('Blog')->where('article_type = "转载"')->getField('COUNT(*) AS num');
    }

    /**
     * 获取翻译文章的数量
     * @return mixed
     */
    public function get_translation_num()
    {
        return M('Blog')->where('article_type = "翻译"')->getField('COUNT(*) AS num');
    }

    /**
     * 获取阅读排行
     */
    public function read_rank()
    {
        return M('Blog')->order('read_times desc')->select();
    }

    /**
     * 获取所有文章创建所在时间段，精确到月份（不重复）
     */
    public function article_archives()
    {
        return M('Blog')->order('date ASC')->field('DISTINCT DATE_FORMAT(FROM_UNIXTIME(`created_at`),\'%Y-%m\') AS date')->select();
    }

    /**
     * 统计所有涉及时间段（一个月内）有几篇文章
     * @param $archives_date 涉及的时间段
     * @return array
     */
    public function get_date_num($archives_date)
    {
        $model = M('Blog');
        $date_num = array();
        foreach ($archives_date as $archive_date) {
            $date_num[$archive_date['date']] = $model->where(array(
                'created_at' => array(
                    array('egt', strtotime($archive_date['date'])),
                    array('lt', strtotime(date('Y-m-01', strtotime('+1 month', strtotime($archive_date['date'])))))
                )))->getField('COUNT(*) AS num');
        }
        return $date_num;
    }

    /**
     * 获取id对应文档名
     * @return mixed
     */
    public function id_to_title()
    {
        return M('Blog')->getField("id, title");
    }
}