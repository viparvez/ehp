<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Auth;
use App\User;
use Validator;

class ProfileController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $user = User::where(['id' => Auth::user()->id])->first();
        return view('pages.profile', compact('user'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|Regex:/^[\D]+$/i|max:70',
            'phone' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['error'=>$validator->errors()->all()]);
        }

        DB::beginTransaction();

        try {

            User::where(['id' => Auth::user()->id])->update(
                [
                    'name' => $request->name,
                    'phone' => $request->phone,
                    'updatedbyuser_id' => Auth::user()->id,
                    'updated_at' => date('Y-m-d h:i:s'),
                ]
            );

            DB::table('useractions')->insert(
                [
                    'user_id' => Auth::user()->id,
                    'action' => 'Profile Updated',
                    'deleted' => '0',
                    'createdbyuser_id' => Auth::user()->id,
                    'created_at' => date('Y-m-d h:i:s'),
                ]
            );

            DB::commit();

            return response()->json(['success'=>'Profile updated']);            
        
        } catch (\Exception $e) {

          DB::rollback();
          return response()->json(['error'=>array($e->getMessage())]);

        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }

    public function updateImage(Request $request, $userid) {

        $validator = Validator::make($request->all(), [
            'inputfile' => 'required|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json(['error'=>$validator->errors()->all()]);
        }

        try{
             if($request->hasFile('inputfile')) {
               
                $file = $request->file('inputfile');
                $name = time().'.'.$file->getClientOriginalExtension();
                $file->move(public_path().'/images/users', $name);

                User::where(['id' => $userid])->update(
                    [
                        'img_url' => url('/')."/public/images/users/".$name,
                        'updatedbyuser_id' => Auth::user()->id,
                        'updated_at' => date('Y-m-d h:i:s'),
                    ]
                );

                DB::table('useractions')->insert(
                    [
                        'user_id' => Auth::user()->id,
                        'action' => 'Image Updated',
                        'deleted' => '0',
                        'createdbyuser_id' => Auth::user()->id,
                        'created_at' => date('Y-m-d h:i:s'),
                    ]
                );

                return response()->json(['success'=>url('/')."/public/images/users/".$name]);  

            }

        } catch(\Exception $e) {
            return response()->json(['error'=>array($e->getMessage())]);
        }

    }

    //Change Pass
    public function changePass(Request $request) {
        
        $validator = Validator::make($request->all(), [
            'password' => 'required|confirmed|min:6',
        ]);

        if ($validator->fails()) {
            return response()->json(['error'=>$validator->errors()->all()]);
        }

        DB::beginTransaction();

        try {

            User::where(['id' => Auth::user()->id])->update(
                [
                    'password' => bcrypt($request->password),
                    'updatedbyuser_id' => Auth::user()->id,
                    'updated_at' => date('Y-m-d h:i:s'),
                ]
            );

            DB::table('useractions')->insert(
                [
                    'user_id' => Auth::user()->id,
                    'action' => 'Password Change',
                    'deleted' => '0',
                    'createdbyuser_id' => Auth::user()->id,
                    'created_at' => date('Y-m-d h:i:s'),
                ]
            );

            DB::commit();

            return response()->json(['success'=>'Profile updated']);  

        } catch(\Exception $e) {
            return response()->json(['error'=>array('Could not update')]);
        }
    }

}
