<?php

class Recipe extends CI_Controller {
function __construct(){
        parent::__construct();
        
         $this->load->helper("url");
         $this->load->model('recipes');
         $this->load->model('users');
         $this->load->library('session');
        
}

 public function loadmaster()
    {
        $popularlist=$this->recipes->loadpopularlist();
        $mostviewedlist=$this->recipes->loadmostviewslist();
        $recentlyaddedlist=$this->recipes->loadrecentlyaddedlist();
        
       // print_r($popularlist);
        $this->load->view('master',array(
            
            'popularlist'=>$popularlist,
             'mostviewlist'=>$mostviewedlist,
            'recentlyaddedlist'=>$recentlyaddedlist,
            
        ));
    }
    
    public function addrecipetestCase($verifydata, $status){
        print_r($verifydata);
        $flag =1;
        //if(match email)
        //$flag=1;
        //else
        //$flag=0;
        
        
        
        if($flag==1)
        {
           $status=TRUE;
        }
        else
            $status=FALSE;
        
        
        
         return $status;
    }

    

    public function addrecipe()
        {
        
      $config = array(
            'upload_path'   => './uploads/imgfiles/',
            'allowed_types' => 'gif|jpg|png',
            'max_size'      => '75100',
            'max_width'     => '1366',
            'max_height'    => '768',
            'encrypt_name'  => true,
        );
      
      $this->load->library('upload', $config);
       if (!$this->upload->do_upload()) {
            $error = array('error' => $this->upload->display_errors());
            
            echo "Failure".$error['error'];
           // $this->load->view('upload_form', $error);
        } else {
        $upload_data = $this->upload->data();}
      
    
           
           
//      $verifydata = array(
//            //'recipe_id' =>$recipeid ,
//            'author' => "rahuldc99@gmail.com",              
//            'rname' => $this->input->post('rname'),
//            'ingredents' => $this->input->post('ingredients'),
//            'steps' => $this->input->post('steps'),
//            'vegselected' => $veg,
//            'recipetype' => $this->input->post('foodtype'),
//            'regiontype' => $this->input->post('regiontype'),
//            'dishImgURL' => $upload_data['file_name'],
//            'VidLinkURL' => $this->input->post('VidLinnk'),
//            //'views' => 0,
//            //'avgrating'=> 0,
//            //'rating' => array(), 
//            //'comments' => array(),
//            //'date'=> date("d-m-Y",time()),
//        );
      
//        if(!$this->addrecipetestCase($verifydata, TRUE))
//        {
//           return; 
//        }
//      

//     
            $name=$this->input->post('rname');
         //   echo "Hello World";
           // echo $name;
        $veg=false;
           if($this->input->post('vegoption')=="yes")
           {
               $veg=true;
           }else
               $veg=false;
            
            $recipeid=md5("recipe".time());
            
            $email=$this->session->userdata('email');
            
            $data = array(
                'recipe_id' =>$recipeid ,
  'author' => $email,              
'rname' => $this->input->post('rname'),
                'ingredents' => $this->input->post('ingredients'),
                'steps' => $this->input->post('steps'),
'vegselected' => $veg,
'recipetype' => $this->input->post('foodtype'),
'regiontype' => $this->input->post('regiontype'),
                
'dishImgURL' => $upload_data['file_name'],
                'VidLinkURL' => $this->input->post('VidLinnk'),
                    'views' => 0,
                    'avgrating'=> 0,
                    'rating' => array(), 
                    'comments' => array(),
                'date'=> date("d-m-Y",time()),
);
           
//print_r($data);
          $this->recipes->addnewrecipe($data); 
          
         
          redirect("/recipe/viewrecipe?r_id=$recipeid");
          
          
        
        
           }
           
   public function viewrecipe()
   {
       //Check for valid login here.............
      // $umail="rhodagaines@greeker.com";
       $umail=$this->session->userdata('email');
        $uname=  $this->users->retrieve_username($umail);
       // echo $uname;
           $this->loadmaster();
           
           
       
       
        $recipearray= $this->recipes->getrecipedetails($this->input->get('r_id')); 
        
         $flagofcrud=0;
        
      
        if($umail==$recipearray[0]['author'])
        {
            $flagofcrud=1;
        }
        else { $flagofcrud=0;}
        
        
        
       // $uname="Rohan Da silva";
       // print_r($recipearray);
        
        $ops = array(
    array(
        '$match' => array(
            "recipe_id" => $this->input->get('r_id')
        
        )
    ),
    array('$unwind' => '$rating'),
    array(
        '$group' => array(
            "_id" => '$recipe_id',
            "avgstars" => array('$avg' => '$rating.stars'),
        ),
    ),
);
       
        $avg=$this->recipes->getavgrating($ops, $this->input->get('r_id'));
        
        
        
        
         $count=0;
         
         if($umail!="")
         {
             $noticnt=$this->users->getnoticount($umail);
            
            $dp=$this->users->retrieve_dp($umail);
            
             $this->load->view('userprofilelayout',array('ncount'=>$noticnt,
                 'uname'=>$uname,'dp'=>$dp));
             $count=$this->recipes->checkifpresentincookbook($umail,$this->input->get('r_id'));
             $flag=1;
         } 
         else
         {
             $noticnt=0;
             $count=0;
             $flag=0;
         }
         
         if($recipearray[0]['author']==$umail)
         {
             $count=2;
         }
            $this->load->view('r_details',
                    array(
                        'flagofcrud'=>$flagofcrud,
                        'flag'=> $flag,
                        'count'=>$count,
                'uname'=>$uname,
                'rname'=>$recipearray[0]['rname'],
                'rsteps'=>$recipearray[0]['steps'],
                'rurl'=>$recipearray[0]['dishImgURL'],
                 'author'=>$recipearray[0]['author'],
                'ingredents'=>$recipearray[0]['ingredents'],
                        'avgrate'=>$avg,
                'vegselected'=>$recipearray[0]['vegselected'],
                        'recipetype'=>$recipearray[0]['recipetype'],
                'regiontype'=>$recipearray[0]['regiontype'],
                'VidLinkURL'=>$recipearray[0]['VidLinkURL'],
                 'dateadded'=>$recipearray[0]['date'],      
                 'comments' =>array_reverse($recipearray[0]['comments']),      
                 'views' =>$recipearray[0]['views'],        
                        
                        
                'r_id'=>$recipearray[0]['recipe_id'],
                
            )
                    );
             $this->load->view('footer');
   }
     
   
   
   public function  addcomment()
   {
       //$uid="ron@gmail.com";
       //$uname="Rohan Da silva";
       $uid=$this->session->userdata('email');
        $uname=$this->users->retrieve_username($uid);
       //$rr = $this->input->post('r_id');
       $this->recipes->addnewcomment($this->input->post('r_id'),$uid,$uname,$this->input->post('comment'));
       
                $data = array(
        'stat' => TRUE,
               
                );
            
            echo json_encode($data);
       
   }
   
    public function  sendrating()
   {
       $uid="ron@gmail.com";
     
   
               $this->recipes->addnewrating($this->input->post('r_id'),$uid,(int)$this->input->post('score'));
       
                $data = array(
        'stat' => TRUE,
               
                );
            
            echo json_encode($data);
   }
    public function editRecipe()
        {
          $this->loadmaster();
          
          $email=$this->session->userdata('email');
        $uname=  $this->users->retrieve_username($email);
          
         
           
           $noticnt=$this->users->getnoticount($email);
            
            $dp=$this->users->retrieve_dp($email);
            
             $this->load->view('userprofilelayout',array('ncount'=>$noticnt,
                 'uname'=>$uname,'dp'=>$dp));
             
             $recipeid=$this->input->get('r_id');
             
             $recipedetails=$this->recipes->getrecipedetails($recipeid);
             
             $rec=$recipedetails[0];
            $this->load->view('edit_r',array('recipedetails'=>$rec));
            $this->load->view('footer');
            
            
        }
        
        public function deleterec() {
            
            $this->recipes->deleterecipe($this->input->get('r_id'));
            
             redirect("/foodieHome/index");
        }
        
        public function updaterecipe()
        {
         $dishimg;
      $config = array(
            'upload_path'   => './uploads/imgfiles/',
            'allowed_types' => 'gif|jpg|png',
            'max_size'      => '75100',
            'max_width'     => '1366',
            'max_height'    => '768',
            'encrypt_name'  => true,
        );

        $this->load->library('upload', $config);

        if (!$this->upload->do_upload()) {
            $error = array('error' => $this->upload->display_errors());
            $dishimg=$this->input->post('dimg');
          //  echo "Failure".$error['error'];
           // $this->load->view('upload_form', $error);
        } else {
            $upload_data = $this->upload->data();
           $dishimg= $upload_data['file_name'];

          //  $this->load->database();
          //  $this->mongo_db->insert('upload', $data_ary);
        }
   
           $name=$this->input->post('rname');
         //   echo "Hello World";
           // echo $name;
        $veg=false;
           if($this->input->post('vegoption')=="yes")
           {
               $veg=true;
           }else
               $veg=false;
            
          
            $recipeid=$this->input->post('rid');
           
            $data = array(
                           
'rname' => $this->input->post('rname'),
                'ingredents' => $this->input->post('ingredients'),
                'steps' => $this->input->post('steps'),
'vegselected' => $veg,
'recipetype' => $this->input->post('foodtype'),
'regiontype' => $this->input->post('regiontype'),
                
'dishImgURL' => $dishimg,
                'VidLinkURL' => $this->input->post('VidLinnk')
                    
);
           
            
          $this->recipes->updaterecipes($data,$recipeid); 
          
        //  echo "updated with image uploading... :p";
          
          redirect("/recipe/viewrecipe?r_id=$recipeid");
          
   
 }


}
