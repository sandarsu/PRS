<?php

namespace App\Controller\User;

use App\Controller\AppController;

/**
 * Prizes Controller
 *
 *
 * @method \App\Model\Entity\Prize[] paginate($object = null, array $settings = [])
 */
class PrizesController extends AppController
{

    /**
     * Index method
     *
     * @return \Cake\Http\Response|void
     */
    public function index()
    {
        $id = $this->Auth->user('id');
        $prizes = $this->paginate($this->Prizes->find('all')->where(['del_flg' => 'not']));

        $this->set(compact('prizes'));
        $this->set('_serialize', ['prizes']);


        $this->loadModel('Scores');
        $scores = $this->Scores->find('all')->where(['user_id' => $id, 'del_flg' => 'not']);
        $this->set('scores', $scores);
    }

    public function score($id = null)
    {
        $this->loadModel('Prizes');

        $prizes = $this->Prizes->find()->where(['id' => $id])->first();

        // $prizes = $this->Prizes->find('all')->where(['id' => 3]);
        $tbl_score = $this->loadModel('Scores');
        $scores = $this->Scores->find()->where(['user_id' => $this->Auth->user('id')])->first();

        $pr_score = $prizes->scores;
        $user_score = $scores->score;

        if ($user_score >= $pr_score) {
            $res = $user_score - $pr_score;

            $scores->score = $res;
            $tbl_score->save($scores);
            $this->Flash->success(__('Taken Prize ' . $prizes->prize_name . ' successfully!'));

            return $this->redirect([
                'controller' => 'Prizes',
                'action' => 'index'
            ]);
        } else {
            $this->Flash->error(__('The prize cannot unavilable.'));
            return $this->redirect([
                'controller' => 'Prizes',
                'action' => 'index'
            ]);
        }
    }


    /**
     * luckyDraw Spin method
     *
     */
    public function spin()
    {
        //load Luckydraw Table
        $tbl_luckydraw = $this->loadModel('luckydraw');
        $luckydraw_result = $this->luckydraw->find('all')->where(['del_flg' => 'not']);
        $luckydraw_count = $this->luckydraw->find()->where(['del_flg' => 'not'])->count();
        $this->set(compact('luckydraw_result', 'luckydraw_count'));
    }

    /**
     * Add User's Scores method
     *
     * @return \Cake\Http\Response|null Redirects to index.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function getscores($id = null)
    {
        if ($this->request->is('post')) {
            $get_scores = $_POST['custom_scores'];
            $user_tbl = $this->loadModel('Scores');
            $user_result = $this->Scores->find()->where(['user_id' => $id, 'del_flg' => 'not'])->first();
            debug($user_result);
            if ($user_result != null) {
                $user_scores = $user_result->score;
                $user_result->score = $user_scores + $get_scores;
                $user_tbl->save($user_result);
            } else {
                $score = $this->Scores->newEntity();
                $score->score = $get_scores;
                $score->user_id = $id;
                //TODO:add expire_datetime
                $user_tbl->save($score);
            }

            echo $get_scores;
        }
        return $this->redirect([
            'controller' => 'Prizes',
            'action' => 'index'
        ]);
    }
}
