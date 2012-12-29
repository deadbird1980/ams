<?php
require_once 'BaseController.php';

class AdminController extends BaseController {

    //Default sort by createtime field
    public $sortField = 'email';
    public $orderType = 'desc';
    public static $tags;

	public function beforeRun($resource, $action){
        parent::beforeRun($resource, $action);

		//if not login, group = anonymous
		$role = (isset($this->session->user['type'])) ? $this->session->user['type'] : 'anonymous';

		if($role!='anonymous'){
				$role = 'admin';
		}

		//check against the ACL rules
		if($rs = $this->acl()->process($role, $resource, $action )){
            echo $role .' is not allowed for '. $resource . ' '. $action;
			return $rs;
		}
	}

    /**
     * Display the list of paginated Posts (draft and published)
     */
	function home() {
        Doo::loadHelper('DooPager');
        Doo::loadModel('User');

        $u = new User();
        //if default, no sorting defined by user, show this as pager link
        if($this->sortField=='email' && $this->orderType=='desc'){
            $pager = new DooPager(Doo::conf()->APP_URL.'admin/user/page', $u->count(), 6, 10);
        }else{
            $pager = new DooPager(Doo::conf()->APP_URL."admin/user/sort/$this->sortField/$this->orderType/page", $u->count(), 6, 10);
        }

        if(isset($this->params['pindex']))
            $pager->paginate(intval($this->params['pindex']));
        else
            $pager->paginate(1);

        $this->data['pager'] = $pager->output;

        $columns = 'id,email,first_name,last_name,first_name_alphabet,last_name_alphabet,phone,qq';
        //Order by ASC or DESC
        if($this->orderType=='desc'){
            $this->data['users'] = $u->limit($pager->limit, null, $this->sortField,
                                        array('select'=>$columns)
                                  );
            $this->data['order'] = 'asc';
        }else{
            $this->data['users'] = $u->limit($pager->limit, $this->sortField, null,
                                        //we don't want to select the Content (waste of resources)
                                        array('select'=>$columns)
                                  );
            $this->data['order'] = 'desc';
        }

        $this->renderAction('admin');
	}

    /**
     * Show single blog post for editing
     */
	function getUser() {
        Doo::loadModel('User');
        $p = new Post();
        $p->id = intval($this->params['pid']);
        
        try{
            $data['post'] = $p->relateTag(
                                        array(
                                            'limit'=>'first',
                                            'asc'=>'tag.name',
                                            'match'=>false      //Post with no tags should be displayed too
                                        )
                                );

            $data['tags'] = array();
            foreach($data['post']->Tag as  $t){
                $data['tags'][] = $t->name;
            }
            $data['tags'] = implode(', ', $data['tags']);
            
        }catch(Exception $e){
            //Exception will be thrown if Post not found
            return array('/error/postNotFound/'.$p->id,'internal');
        }
        
        $this->renderAction('admin_edit_post');
	}

    /**
     * This shows the same content as home(), but with pagination
     * Show error if Page index is invalid (negative)
     */
	function page() {
        if(isset($this->params['pindex']) && $this->params['pindex']>0)
    		$this->home();
        else
            return 404;
	}

    /**
     * Sort by field names DESC or ASC, with paginations
     */
    function sortBy(){
        //if field name not in Post model fields, show error
        if(!in_array($this->params['sortField'], Doo::loadModel('Post', true)->_fields))
            return 404;

        if($this->params['orderType']!='asc' && $this->params['orderType']!='desc')
            return 404;

        $this->orderType = $this->params['orderType'];
        $this->sortField = $this->params['sortField'];
        $this->home();
    }

    /**
     * Validate if post exists
     */
    static function checkPostExist($id){
        Doo::loadModel('Post');
        $p = new Post;
        $p->id = $id;

        //if Post id doesn't exist, return an error
        if($p->find(array('limit'=>1, 'select'=>'id'))==Null)
            return 'Post ID not found in database';
    }

    /**
     * Validate if tags is less than or equal to 10 tags based on the String seperated by commas.
     * Tags cannot be empty 'mytag, tag2,,tag4,  , tag5' (error)
     */
    static function checkTags($tagStr){
        //tags can be empty(no tags)
        $tagStr = trim($tagStr);
        if(empty($tagStr)){
           return;
        }

        $tags = explode(',', $tagStr);

        foreach($tags as $k=>$v){
            $tags[$k] = strip_tags(trim($v));
            if(empty($tags[$k])){
                return 'Invalid tags!';
            }
        }

        if(sizeof($tags)>10)
            return 'You can only have max 10 tags!';

        self::$tags = $tags;
    }

    /**
     * Save changes made in Post editing
     */
    function savePostChanges(){               
        Doo::loadHelper('DooValidator');

        $_POST['content'] = trim($_POST['content']);

        //get defined rules and add show some error messages
        $validator = new DooValidator;
        $validator->checkMode = DooValidator::CHECK_SKIP;

        if($error = $validator->validate($_POST, 'post_edit.rules')){
            $data['rootUrl'] = Doo::conf()->APP_URL;
            $data['title'] =  'Error Occured!';
            $data['content'] =  '<p style="color:#ff0000;">'.$error.'</p>';
            $data['content'] .=  '<p>Go <a href="javascript:history.back();">back</a> to edit.</p>';
            $this->renderAction('admin_msg', $data);
        }
        else{
            Doo::loadModel('Post');
            Doo::loadModel('Tag');

            $p = new Post($_POST);

            //delete the previous linked tags first
            Doo::loadModel('PostTag');
            $pt = new PostTag;
            $pt->post_id = $p->id;
            $pt->delete();

            //update the post along with the tags
            if(self::$tags!=Null){
                $tags = array();
                foreach(self::$tags as $t){
                    $tg = new Tag;
                    $tg->name = $t;
                    $tags[] = $tg;
                }
                $p->relatedUpdate($tags);
            }
            //if no tags, just update the post
            else{
                $p->update();
            }
            
            //clear the sidebar cache
            Doo::cache('front')->flushAllParts();
            
            $this->data['rootUrl'] = Doo::conf()->APP_URL;
            $this->data['title'] =  'Post Updated!';
            $this->data['content'] =  '<p>Your changes is saved successfully.</p>';
            $this->data['content'] .=  '<p>Click  <a href="'.$this->data['rootUrl'].'article/'.$p->id.'">here</a> to view the post.</p>';
            $this->renderAction('admin_msg');
        }
    }

    function saveNewPost(){
        Doo::loadHelper('DooValidator');

        $_POST['content'] = trim($_POST['content']);

        //get defined rules and add show some error messages
        $validator = new DooValidator;
        $validator->checkMode = DooValidator::CHECK_SKIP;

        if($error = $validator->validate($_POST, 'post_create.rules')){
            $data['rootUrl'] = Doo::conf()->APP_URL;
            $data['title'] =  'Error Occured!';
            $data['content'] =  '<p style="color:#ff0000;">'.$error.'</p>';
            $data['content'] .=  '<p>Go <a href="javascript:history.back();">back</a> to edit.</p>';
            $this->renderAction('admin_msg', $data);
        }
        else{
            Doo::loadModel('Post');
            Doo::loadModel('Tag');
            Doo::autoload('DooDbExpression');
            $p = new Post($_POST);
            $p->createtime = new DooDbExpression('NOW()');

            //insert the post along with the tags
            if(self::$tags!=Null){
                $tags = array();
                foreach(self::$tags as $t){
                    $tg = new Tag;
                    $tg->name = $t;
                    $tags[] = $tg;
                }
                $id = $p->relatedInsert($tags);
            }
            //if no tags, just insert the post
            else{
                $id = $p->insert();
            }

            //clear the sidebar cache
            Doo::cache('front')->flushAllParts();

            $data['rootUrl'] = Doo::conf()->APP_URL;
            $data['title'] =  'Post Created!';
            $data['content'] =  '<p>Your post is created successfully!</p>';
            if($p->status==1)
                $data['content'] .=  '<p>Click  <a href="'.$data['rootUrl'].'article/'.$id.'">here</a> to view the published post.</p>';
            $this->renderAction('admin_msg', $data);
        }
    }


    /**
     * Delete post
     */
    function deletePost(){
        $pid = intval($this->params['pid']);
        if($pid>0){
            Doo::loadModel('Post');
            $p = new Post;
            $p->id = $pid;
            $p->delete();

            //clear the sidebar cache
            Doo::cache('front')->flushAllParts();

            $data['rootUrl'] = Doo::conf()->APP_URL;
            $data['title'] =  'Post Deleted!';
            $data['content'] =  "<p>Post with ID $pid is deleted successfully!</p>";
            $this->renderAction('admin_msg', $data);
        }
    }
    
    function listUser(){
        Doo::loadModel('User');
        $c = new User;
        $data['users'] = $c->find(array('desc'=>'createtime'));
        $data['rootUrl'] = Doo::conf()->APP_URL;
        $this->renderAction('admin_users', $data);
    }

    function approveComment(){
        Doo::loadModel('Comment');
        $c = new Comment;
        $c->id = intval($this->params['cid']);
        $comment = $c->find(array('limit'=>1, 'select'=>'id, post_id'));

        //if not exists, show error
        if($comment==Null){
            return 404;
        }

        //change status to Approved
        $comment->status = 1;
        $comment->update(array('field'=>'status'));

        Doo::loadModel('Post');
        Doo::autoload('DooDbExpression');

        //Update totalcomment field in Post
        $p = new Post;
        $p->id = $comment->post_id;
        $p->totalcomment = new DooDbExpression('totalcomment+1');
        $p->update(array('field'=>'totalcomment'));

        $data['rootUrl'] = Doo::conf()->APP_URL;
        $data['title'] =  'Comment Approved!';
        $data['content'] =  "<p>Comment is approved successfully!</p>";
        $data['content'] .=  "<p>View the comment <a href=\"{$data['rootUrl']}article/$p->id#comment$comment->id\">here</a></p>";
        $this->renderAction('admin_msg', $data);
    }

    /**
     * Reject (delete) unapproved comment
     */
    function rejectComment(){
        Doo::loadModel('Comment');
        $c = new Comment;
        $c->id = intval($this->params['cid']);
        $c->status = 0;
        $c->delete(array('limit'=>1));

        $data['rootUrl'] = Doo::conf()->APP_URL;
        $data['title'] =  'Comment Rejected!';
        $data['content'] =  "<p>Comment is rejected &amp; deleted successfully!</p>";
        $thisrenderAction('admin_msg', $data);
    }

}
?>
