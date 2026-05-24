<?php
namespace App\Service;

use App\ApiResponseModel\GroupChatCreationApiResponseModel;
use App\ApiResponseModel\PrivateConversationCreationApiResponseModel;
use App\Dto\ChatMember;
use App\Dto\GroupChatCreateDto;
use App\Enums\ConversationUserStatus;
use App\Enums\MessageTypeEnum;
use App\Interface\ConversationRepositoryInterface;
use App\Interface\ConversationUserRepositoryInterface;
use App\Models\ConUser;
use App\Models\Conversation;
use App\Models\User;
use App\Repository\ConversationRepository;
use App\Repository\ConversationUserRepository;
use Exception;
use Illuminate\Support\Facades\DB;

class ConversationService{

    protected ConversationRepositoryInterface $conversationRepository;
    protected ConversationUserRepositoryInterface $conversationUserRepository;

    public function __construct()
    {
        $this->conversationRepository=new ConversationRepository();
        $this->conversationUserRepository=new ConversationUserRepository();
    }

    /**
     * @throws Exception
     */
    public function createPrivateConversation($list):PrivateConversationCreationApiResponseModel{

        DB::beginTransaction();
          try{
              $conversation = $this->conversationRepository->createPrivateConversation();
              $conversationId=$conversation['id'];
              $this->conversationUserRepository->createPrivateConversation($list,$conversationId);
              DB::commit();

              $user = User::find($list[1]->userId);
              $responseModel = new PrivateConversationCreationApiResponseModel();
              $responseModel->conversationId = $conversationId;
              $responseModel->id = $user->id;
              $responseModel->name = $user->name;
              $responseModel->avatar = $user->avatar;
              $responseModel->latestKeyVersion = $conversation?$conversation['latest_key_version']:1;

              return $responseModel;
          }catch (Exception $e){
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
    public function createGroupChat(GroupChatCreateDto $dto,int $userID):GroupChatCreationApiResponseModel{
        Db::beginTransaction();
        $conversation=$this->createGroupConversation($dto->name);
        $conversationId=$conversation['id'];
        $this->conversationUserRepository->createGroupConversation($dto->getMembers(),$conversationId,$userID);
        $messageDto=[];
        $messageDto['conversation_id']=$conversationId;
        $messageDto['message_tye']=MessageTypeEnum::TEXT;
        $messageDto['sender_id']=auth()->id();
        $messageDto['message']="Welcome to the group";
        $messageDto['key_version']=1;
        $messageDto['iv']="randomIv";
        app(MessageService::class)->createMessage($messageDto);
        Db::commit();

        $response=new GroupChatCreationApiResponseModel();
        $response->name=$dto->name;
        $response->conversationId=$conversationId;
        $response->latestKeyVersion=$conversation['latest_key_version'];
        return $response;

    }

    public function updateConversation($conversationId, array $data)
    {
        $conversation = Conversation::findOrFail($conversationId);

        $conversation->name = $data['name'];

        if (!empty($data['image'])) {

            $oldImage = $conversation->avatar;

            if ($oldImage && file_exists(public_path('images/avatars/' . $oldImage))) {
                unlink(public_path('images/avatars/' . $oldImage));
            }

            $conversation->image = $data['image'];
        }

        $conversation->save();

        return $conversation;
    }

    /**
     * @param ChatMember[] $members
     * @return void
     * @throws Exception
     */
    public function addGroupMembers(array $members,int $conversationId){
        $conversation = Conversation::find($conversationId);
        if(!$conversation){
            throw new Exception("Conversation not found");
        }
        $latestKeyVersion=$conversation->latest_key_version + 1;
        $conversation->latest_key_version=$latestKeyVersion;
       DB::beginTransaction();
       try{
           $conversation->save();
           foreach ($members as $member){
               $this->conversationUserRepository->addMemberToConversation($conversationId,[$member],$latestKeyVersion);
               $this->conversationUserRepository->activateMember($member->getUserId(),$conversationId);
           }
           DB::commit();
       }catch (Exception $e){
           DB::rollBack();
           throw $e;
       }

    }

    public function removeGroupMembers(array $memberIds,$conversationId):void{
        DB::beginTransaction();
        try{
            foreach ($memberIds as $memberId){
                $this->conversationUserRepository->deactivateMember($memberId,$conversationId);
            }
            DB::commit();
        }catch (Exception $e){
            DB::rollBack();
            throw $e;
        }
    }
    public function leaveGroup(int $conversationId,int $myId):void{
        $conversation = Conversation::find($conversationId);
        if(!$conversation){
            throw new Exception("Conversation not found");
        }
       $conUser=ConUser::where('conversation_id',$conversationId)->where('user_id',$myId)->select('is_admin')->first();
        if($conUser->is_admin) {
            $this->conversationUserRepository->removeAdmin($myId,$conversationId);
            $this->MakeAnotherUserAdminIfNoAdminInGroup($conversationId,$myId);
            $this->conversationUserRepository->deactivateMember($myId,$conversationId);
        }
    }

    private function createGroupConversation(string $name):Conversation{
        return $this->conversationRepository->createGroupConversation($name);
    }

    private function MakeAnotherUserAdminIfNoAdminInGroup(int $conversationId,int $myId):void{

        $anotherGroupAdmin=ConUser::where('conversation_id',$conversationId)
            ->where('is_admin',1)
            ->where('user_id','!=',$myId)
            ->where('status',ConversationUserStatus::ACTIVE)
            ->exists();

        if($anotherGroupAdmin)
            return;

        $anotherRandomUser=ConUser::where('conversation_id',$conversationId)
            ->where('user_id','!=',$myId)
            ->where('status',ConversationUserStatus::ACTIVE)
            ->select('user_id')->first();

        $this->conversationUserRepository->makeAdmin($anotherRandomUser->user_id,$conversationId);

    }

}
