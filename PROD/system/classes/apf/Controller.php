<?php
/**
 * 控制器接口，而非抽象类……
 * @author lusurf
 *
 */
abstract class APF_Controller {
    public function __construct() {
    }

    public function __destruct() {
    }

    public function get_interceptor_index_name () {
    	return __CLASS__;
    }

    /**
     * 子类通过实现本方法添加业务逻辑
     * @return mixed string|array 直接返回字符串表示页面类名称；返回数组包含
     * 两个成员，第一个是页面类名称，第二个为页面类使用的变量。
     * @example 返回'Hello_Apf_Demo'，APF会加载Hello_Apf_DemoPage类。
     * @example 返回array('Hello_Apf_Demo', array('foo' => 'bar'))，APF会加载
     * Hello_Apf_Demo类，而且在对应的phtml文件中可以直接使用变量$foo，其值为'bar'。
     *
     * 注意，返回字符串是为了兼容旧有代码，不推荐使用。
     */
    abstract public function handle_request();
}
