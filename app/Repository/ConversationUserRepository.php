<?php
namespace App\Repository;
use App\Dto\ChatMember;
use App\Enums\ConversationUserStatus;
use App\Interface\ConversationUserRepositoryInterface;
use App\Models\ConUser;
use Exception;
use Illuminate\Support\Facades\DB;

class ConversationUserRepository implements ConversationUserRepositoryInterface
{
    public function FindConversation(int $userId)
    {
        return ConUser::where('user_id',$userId)
             ->whereIn('conversation_id',function ($query) use ($userId) {
                 $query->select('conversation_id')
                     ->from('conversation_user')
                     ->where('user_id', auth()->id());
                     })
                     ->with('user')
                     ->first();
    }

    public function createPrivateConversation($list, $conversationId):void{
        foreach($list as $dto){
            ConUser::create([
                'user_id'=>$dto->userId,
                'conversation_id'=>$conversationId,
                'encrypted_room_key'=>$dto->encryptedKey
            ]);
        }
    }
    public function createGroupConversation($list,$conversationId):void{
        /** @var ChatMember $dto */
        foreach($list as $dto){
            ConUser::create([
                'user_id'=>$dto->getUserId(),
                'conversation_id'=>$conversationId,
                'encrypted_room_key'=>$dto->getEncryptedKey(),
                'is_admin'=>$dto->isAdmin()
            ]);
        }

    }

    public function getReceiverId(int $conversationId): int
    {
        return ConUser::where('conversation_id',$conversationId)->where('user_id','!=',auth()->id())->pluck('user_id')->first();
    }

    public function getConversationIdOfUser(int $userId): array
    {
        return ConUser::where('user_id',$userId)->pluck('conversation_id')->toArray();
    }

    public function checkValidUser(int $userId, int $conversationId): bool
    {
      return ConUser::where('user_id',$userId)->where('conversation_id',$conversationId)->exists();
    }

    /**
     * @param int $conversationId
     * @param ChatMember[] $member
     * @param int $keyVersion
     * @return void
     */
    public function addMemberToConversation(int $conversationId, array $member, int $keyVersion): void
    {
        DB::beginTransaction();
        foreach ($member as $user){
            ConUser::create([
                'user_id'=>$user->getUserId(),
                'conversation_id'=>$conversationId,
                'encrypted_room_key'=>$user->getEncryptedKey(),
                'key_version'=>$keyVersion,
            ]);
        }
        DB::commit();

    }
    /**
     * @throws Exception
     */
    public function deactivateMemberId(int $userId, int $groupId):void
    {
        DB::beginTransaction();
        try{
            $userWithAllKeysHeHave=ConUser::where('conversation_id',$groupId)
                ->where('user_id',$userId);
            if(!$userWithAllKeysHeHave->exists())
                return;
            $userWithAllKeysHeHave=$userWithAllKeysHeHave->get();
            foreach($userWithAllKeysHeHave as $user){
                $user->status=ConversationUserStatus::INACTIVE;
                $user->save();
            }
            DB::commit();
        }catch (Exception $e){
            DB::rollBack();
            throw $e;
        }

    }
    public function activateMemberId(int $userId, int $groupId):void
    {
        DB::beginTransaction();
        try{
            $userWithAllKeysHeHave=ConUser::where('conversation_id',$groupId)
                ->where('user_id',$userId);
            if(!$userWithAllKeysHeHave->exists())
                return;
            $userWithAllKeysHeHave=$userWithAllKeysHeHave->get();
            foreach($userWithAllKeysHeHave as $user){
                $user->status=ConversationUserStatus::ACTIVE;
                $user->save();
            }
            DB::commit();
        }catch (Exception $e){
            DB::rollBack();
            throw $e;
        }

    }
}
