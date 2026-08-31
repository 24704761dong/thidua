import React, { useEffect, useState } from 'react';
import { Modal, Text, Box } from 'zmp-ui';

const CURRENT_VERSION_CODE = 2;

export const AutoUpdater: React.FC = () => {
  const [updateInfo, setUpdateInfo] = useState<{
    versionCode: number;
    versionName: string;
    downloadUrl: string;
    forceUpdate: boolean;
    message: string;
  } | null>(null);
  const [modalVisible, setModalVisible] = useState(false);

  useEffect(() => {
    // Only check for updates once when the app loads
    const checkForUpdates = async () => {
      try {
        // Prevent browser caching the version file
        const res = await fetch('https://c3binhson.edu.vn/thidua/public/version.json?t=' + new Date().getTime());
        if (!res.ok) return;
        const data = await res.json();
        
        if (data && data.versionCode > CURRENT_VERSION_CODE) {
          setUpdateInfo(data);
          setModalVisible(true);
        }
      } catch (err) {
        console.error('Lỗi khi kiểm tra phiên bản mới:', err);
      }
    };
    
    checkForUpdates();
  }, []);

  if (!updateInfo) return null;

  return (
    <Modal
      visible={modalVisible}
      title={'Cập nhật phiên bản ' + updateInfo.versionName}
      onClose={() => {
        if (!updateInfo.forceUpdate) {
          setModalVisible(false);
        }
      }}
      maskClosable={!updateInfo.forceUpdate}
      actions={[
        ...(!updateInfo.forceUpdate
          ? [
              {
                text: 'Để sau',
                onClick: () => {
                  setModalVisible(false);
                },
              },
            ]
          : []),
        {
          text: 'Tải về ngay',
          highLight: true,
          onClick: () => {
            window.open(updateInfo.downloadUrl, '_system');
          },
        },
      ]}
    >
      <Box p={4} style={{ textAlign: 'center' }}>
        <Text size="normal" className="text-slate-600">
          {updateInfo.message}
        </Text>
      </Box>
    </Modal>
  );
};
