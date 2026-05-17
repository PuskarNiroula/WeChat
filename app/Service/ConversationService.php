<?php
namespace App\Service;

use App\ApiResponseModel\PrivateConversationCreationApiResponseModel;
use App\Interface\ConversationRepositoryInterface;
use App\Interface\ConversationUserRepositoryInterface;
use App\Models\Conversation;
use App\Models\User;
use App\Repository\ConversationRepository;
use App\Repository\ConversationUserRepository;
use Illuminate\Support\Facades\DB;

class ConversationService{

    protected ConversationRepositoryInterface $conversationRepository;
    protected ConversationUserRepositoryInterface $conversationUserRepository;

    public function __construct()
    {
        $this->conversationRepository=new ConversationRepository();
        $this->conversationUserRepository=new ConversationUserRepository();
    }

    public function createPrivateConversation($list):PrivateConversationCreationApiResponseModel{

          try{
              DB::beginTransaction();
              $conversationId = $this->conversationRepository->createPrivateConversation();
              $this->conversationUserRepository->createPrivateConversation($list,$conversationId);
              DB::commit();

              $user = User::find($list[1]->userId);
              $responseModel = new PrivateConversationCreationApiResponseModel();
              $responseModel->conversationId = $conversationId;
              $responseModel->id = $user->id;
              $responseModel->name = $user->name;
              $responseModel->avatar = $user->avatar;

              return $responseModel;
          }catch (\Exception $e){
              DB::rollBack();
              throw $e;
          }

    }
    public function checkIfPrivateConversationExist(int $receiverId, int $senderId):?PrivateConversationCreationApiResponseModel
    {
        $conversation = Conversation::where('type', 'private')
            ->whereHas('conUsers', function ($query) use ($senderId) {
                $query->where('user_id', $senderId);
            })
            ->whereHas('conUsers', function ($query) use ($receiverId) {
                $query->where('user_id', $receiverId);
            })
            ->select('id')
            ->first();
        if(!$conversation){
            return null;
        }

        $user=User::find($receiverId);
        $model = new PrivateConversationCreationApiResponseModel();
        $model->id = $user->id;
        $model->name = $user->name;
        $model->avatar = $user->avatar;
        $model->conversationId = $conversation ? $conversation->id : null;
        return $model;

    }

}
