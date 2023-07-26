<?php

class OpenAi {
    # API 目標 URL
    protected $apiUrl = 'https://api.openai.com/v1/chat/completions';
    # API 密鑰
    protected $apiKey = 'YOUR_API_KEY_HERE';
    # 模型
    protected $model;

    # 模型
    const MODEL_DAVINCI = 'text-davinci-003';
    const MODEL_TURBO   = 'gpt-3.5-turbo';

    /**
     * OpenAi constructor.
     * @param string $apiKey
     *
     * @throws Exception
     */
    public function __construct(string $apiKey)
    {
        if (trim($apiKey) == '') {
            throw new Exception('API key is required.');
        }
        $this->apiKey = $apiKey;

        # 預設模型
        $this->model = self::MODEL_TURBO;
    }

    /**
     * 修改模型
     * @param string $model
     *
     * @return $this
     * @throws Exception
     */
    public function setModel(string $model): self
    {
        if (!in_array($model, [self::MODEL_DAVINCI, self::MODEL_TURBO])) {
            throw new Exception('Model is invalid.');
        }
        $this->model = $model;

        return $this;
    }


    /**
     * 取得 OpenAi 回應
     * @param string $userContent
     *
     * @return array
     */
    public function getResponse(string $userContent): array
    {

        try {
            # 檢查提問內容
            if (trim($userContent) == '') {
                throw new Exception('User content is required.');
            }

            # 組成問題資料
            $jsonInput = json_encode([
                'model' => $this->model, // 使用的模型，可以是 'text-davinci-003' 或 'gpt-3.5-turbo'
                'messages' => [
                    [
                        'role' => 'system',
                        'content' => 'You are a helpful assistant.',
                    ],
                    [
                        'role' => 'user',
                        'content' => $userContent,
                    ],
                ],
            ]);

            $response = $this->CURL($jsonInput);

            # 檢查回應
            if (!isset($response['choices'][0]['message']['content'])) {
                throw new Exception('No response from ChatGPT.');
            }

            $re = [
                'error' => 0,
                'msg'   => $response['choices'][0]['message']['content'],
            ];
        } catch (Exception $e) {
            # todo 記錄錯誤
            $re = [
                'error' => 1,
                'msg' => $e->getMessage(),
            ];
            echo $e->getMessage();
        }

        return $re;
    }

    /**
     * 執行 cURL 請求，獲取 API 回應
     * @param string $jsonInput
     *
     * @return array
     * @throws Exception
     */
    protected function CURL(string $jsonInput): array
    {
        // 設定 cURL 請求
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->apiUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonInput);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $this->apiKey,
        ]);

        # 檢查是否有錯誤
        $result = curl_exec($ch);
        if (curl_errno($ch)) {
            curl_close($ch);
            throw new Exception('cURL Error: ' . curl_error($ch));
        }

        # 回傳結果
        curl_close($ch);
        return json_decode($result, true);
    }
}