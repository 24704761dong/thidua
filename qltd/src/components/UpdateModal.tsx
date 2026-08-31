import React from 'react';
import { Modal, Box, Text, Button } from 'zmp-ui';
import { Icon } from '@/components/Icon';

interface UpdateModalProps {
  visible: boolean;
  isForce: boolean;
  versionData: {
    latest_version: string;
    download_url_android: string;
    download_url_ios: string;
    release_notes: string;
    force_update_message: string;
  } | null;
  onClose: () => void;
}

const UpdateModal: React.FC<UpdateModalProps> = ({ visible, isForce, versionData, onClose }) => {
  if (!versionData) return null;

  const handleUpdate = () => {
    // In a real app, detect OS. Here we assume Android APK download for simplicity.
    // If it's a true native wrapper, we can use window.open to trigger the download.
    window.open(versionData.download_url_android, '_system');
  };

  return (
    <Modal
      visible={visible}
      title=""
      onClose={isForce ? () => {} : onClose} // Prevent closing if it's a force update
      className="update-modal"
    >
      <Box className="flex flex-col items-center text-center pb-4">
        <div className="w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center mb-4">
          <Icon icon="zi-download" className="text-blue-500 !text-[32px]" />
        </div>
        
        <Text size="xLarge" className="font-bold text-slate-800 mb-2">
          {isForce ? 'Cập Nhật Bắt Buộc' : 'Có Bản Cập Nhật Mới'}
        </Text>
        
        <Text size="normal" className="text-slate-600 mb-2">
          Phiên bản {versionData.latest_version} đã sẵn sàng.
        </Text>

        <Text size="small" className="text-slate-500 mb-6 bg-slate-50 p-3 rounded-lg text-left w-full whitespace-pre-line border border-slate-100">
          {isForce ? versionData.force_update_message : versionData.release_notes.replace(/\\n/g, '\n')}
        </Text>

        <div className="flex w-full space-x-3">
          {!isForce && (
            <Button
              variant="secondary"
              fullWidth
              onClick={onClose}
              className="!bg-slate-100 !text-slate-600"
            >
              Để sau
            </Button>
          )}
          <Button
            variant="primary"
            fullWidth
            onClick={handleUpdate}
            className="!bg-blue-600 shadow-md shadow-blue-200"
          >
            Tải Ngay
          </Button>
        </div>
        
        {isForce && (
          <Text size="xSmall" className="text-slate-400 mt-4">
            Bạn không thể tắt thông báo này cho đến khi cập nhật hoàn tất.
          </Text>
        )}
      </Box>
    </Modal>
  );
};

export default UpdateModal;
